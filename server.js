require('dotenv').config();
const express = require('express');
const nodemailer = require('nodemailer');
const cors = require('cors');
const path = require('path');
const fs = require('fs');

const app = express();
// Preferred port (can be overridden by environment) with automatic fallback if in use
const PREFERRED_PORT = Number(process.env.PORT) || 3000;
// Capture whether env vars were present at boot
const mailConfiguredAtBoot = !!(process.env.MAIL_USER && process.env.MAIL_PASS);
const envPath = path.join(__dirname,'.env');
const hasDotEnvFile = fs.existsSync(envPath);
console.log('[Startup] MAIL_USER present:', !!process.env.MAIL_USER, '| MAIL_PASS present:', !!process.env.MAIL_PASS, '| .env exists:', hasDotEnvFile, '| path:', envPath);
if(!hasDotEnvFile){
  console.warn('[Startup] .env file NOT found. Create one at the path above with MAIL_USER and MAIL_PASS.');
}

// TEMP: Allow all origins for debugging network/CORS issues
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname)));

// Simple health endpoint to confirm server + mail config status
app.get('/health', (req,res)=>{
  console.log('[Health Check] /health endpoint hit from', req.headers['origin'], '| IP:', req.ip);
  res.json({ ok:true, mailConfigured: !!(process.env.MAIL_USER && process.env.MAIL_PASS), mailConfiguredAtBoot });
});

// Dev-only debug endpoint (remove in production) to inspect env presence (password not exposed)
app.get('/debug/mail-config', (req,res)=>{
  res.json({
    ok:true,
    hasDotEnvFile,
    envPath,
    mailUserPresent: !!process.env.MAIL_USER,
    mailPassPresent: !!process.env.MAIL_PASS,
    mailPassLength: process.env.MAIL_PASS ? process.env.MAIL_PASS.length : 0
  });
});


// Basic rate limit (very lightweight) in-memory
const submissions = new Map();
function allowSubmission(ip){
  const now = Date.now();
  const history = submissions.get(ip) || [];
  const recent = history.filter(ts => now - ts < 5*60*1000); // last 5 min
  if(recent.length >= 5) return false; // max 5 in 5 minutes
  recent.push(now);
  submissions.set(ip, recent);
  return true;
}

app.post('/api/consultation', async (req,res) => {
  const ip = req.headers['x-forwarded-for'] || req.socket.remoteAddress || 'unknown';
  console.log('[Consultation] POST /api/consultation from', req.headers['origin'], '| IP:', ip, '| Body:', req.body);
  if(!allowSubmission(ip)) return res.status(429).json({ ok:false, message:'Too many submissions, please wait a few minutes.' });
  try {
    const { name, email, phone, businessName } = req.body || {};
    if(!name || !email || !phone) {
      return res.status(400).json({ ok:false, message:'Missing required fields.' });
    }

    // Configure transporter (Gmail example). Requires env vars.
    if(!process.env.MAIL_USER || !process.env.MAIL_PASS){
      return res.status(500).json({ ok:false, message:'Email not configured (missing MAIL_USER / MAIL_PASS)' });
    }
    // Gmail App Passwords are exactly 16 characters (no spaces). If it's not 16, user likely used normal password.
    if(process.env.MAIL_PASS.length !== 16){
      console.warn('[Warn] MAIL_PASS length is', process.env.MAIL_PASS.length, 'expected 16 for a Gmail App Password.');
      return res.status(500).json({ ok:false, message:'MAIL_PASS is not a 16-char Gmail App Password. Generate an App Password in Google Account Security.' });
    }
    const transporter = nodemailer.createTransport({
      service: 'gmail',
      auth: {
        user: process.env.MAIL_USER,
        pass: process.env.MAIL_PASS
      }
    });

    // Verify once per process (cache result)
    if(typeof app.locals.smtpReady === 'undefined'){
      try {
        await transporter.verify();
        app.locals.smtpReady = true;
        console.log('SMTP connection verified.');
      } catch (vErr){
        app.locals.smtpReady = false;
        console.error('SMTP verify failed:', vErr.message);
        return res.status(500).json({ ok:false, message:'Email server auth failed: '+ vErr.message });
      }
    } else if(app.locals.smtpReady === false){
      return res.status(500).json({ ok:false, message:'Email server not ready (previous verify failed)' });
    }

    const target = 'royalorbitinnovations@gmail.com';
    const plain = `Name: ${name}\nBusiness Name: ${businessName || '-'}\nEmail: ${email}\nContact: ${phone}\n\nThis client requests to contact you.`;

    await transporter.sendMail({
      from: `ROI Website <${process.env.MAIL_USER}>`,
      to: target,
      subject: 'New Consultation Request',
      text: plain
    });

    res.json({ ok:true });
  } catch (err) {
    console.error('Email send error', err);
    let msg = 'Failed to send email';
    if(err && err.response) msg += ': ' + err.response;
    else if(err && err.message) msg += ': ' + err.message;
    res.status(500).json({ ok:false, message: msg });
  }
});

// Dev test endpoint to trigger a simple email to verify configuration (remove in production)
app.get('/debug/send-test', async (req,res)=>{
  try {
    if(!process.env.MAIL_USER || !process.env.MAIL_PASS){
      return res.status(500).json({ ok:false, message:'Missing MAIL_USER / MAIL_PASS' });
    }
    if(process.env.MAIL_PASS.length !== 16){
      return res.status(500).json({ ok:false, message:'MAIL_PASS must be a 16-char Gmail App Password' });
    }
    const transporter = nodemailer.createTransport({
      host:'smtp.gmail.com',
      port:465,
      secure:true,
      auth:{ user:process.env.MAIL_USER, pass:process.env.MAIL_PASS }
    });
    await transporter.verify();
    await transporter.sendMail({
      from:`ROI Website <${process.env.MAIL_USER}>`,
      to: process.env.MAIL_USER,
      subject:'Test Email (ROI Website)',
      text:'Test email from /debug/send-test endpoint to confirm SMTP works.'
    });
    res.json({ ok:true, message:'Test email sent to MAIL_USER inbox.' });
  } catch(err){
    console.error('Test email error:', err);
    res.status(500).json({ ok:false, message: err.message });
  }
});

// Graceful port binding with fallback to the next available port(s) if EADDRINUSE
function startServer(startPort, maxAttempts = 15){
  let attempt = 0;
  const tryListen = (port) => {
    const server = app.listen(port, '0.0.0.0', () => {
      if(port !== startPort){
        console.log(`[Startup] Desired port ${startPort} was busy; server started on fallback port ${port}.`);
      } else {
        console.log(`Server running on http://0.0.0.0:${port}`);
      }
      process.env.ACTUAL_PORT = String(port);
    });

    server.on('error', (err) => {
      if(err && err.code === 'EADDRINUSE'){
        attempt++;
        if(attempt < maxAttempts){
          const nextPort = port + 1;
            console.warn(`[Startup] Port ${port} in use, retrying with ${nextPort} (attempt ${attempt}/${maxAttempts-1})...`);
            setTimeout(()=> tryListen(nextPort), 250);
        } else {
          console.error(`[Startup] All attempted ports (${startPort}..${port}) are in use. Giving up.`);
          process.exit(1);
        }
      } else {
        console.error('[Startup] Server error during listen:', err);
        process.exit(1);
      }
    });
  };
  tryListen(startPort);
}

startServer(PREFERRED_PORT);
