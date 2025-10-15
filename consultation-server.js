const express = require('express');
const nodemailer = require('nodemailer');
const cors = require('cors');
const app = express();
const PORT = process.env.PORT || 5000;

app.use(cors());
app.use(express.json());

// Configure your email transport
const transporter = nodemailer.createTransport({
  service: 'gmail',
  auth: {
    user: 'royalorbitinnovation@gmail.com', // Your Gmail address
    pass: 'YOUR_APP_PASSWORD' // Use an App Password, not your Gmail password
  }
});

app.post('/api/consultation', async (req, res) => {
  const { name, email, phone } = req.body;
  if (!name || !email || !phone) {
    return res.status(400).json({ error: 'All fields are required.' });
  }

  const mailOptions = {
    from: 'royalorbitinnovation@gmail.com',
    to: 'royalorbitinnovation@gmail.com',
    subject: 'New Consultation Request',
    text: `Name: ${name}\nEmail: ${email}\nPhone: ${phone}`
  };

  try {
    await transporter.sendMail(mailOptions);
    res.status(200).json({ message: 'Consultation request sent successfully.' });
  } catch (error) {
    res.status(500).json({ error: 'Failed to send email.' });
  }
});

app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
