// Firebase web SDK via ESM (browser) CDN imports
// Always include this file using <script type="module" src="./firebase-config.js"></script>
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.14.1/firebase-app.js";
import { getFirestore, serverTimestamp } from "https://www.gstatic.com/firebasejs/10.14.1/firebase-firestore.js";
import { getStorage } from "https://www.gstatic.com/firebasejs/10.14.1/firebase-storage.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/10.14.1/firebase-auth.js";

// Your web app's Firebase configuration
export const firebaseConfig = {
  apiKey: "AIzaSyCVy9qbbtyAU2vxE8jJacI2wo0cm9HTHSg",
  authDomain: "roi-website-admin.firebaseapp.com",
  projectId: "roi-website-admin",
  // Note: modern Firebase consoles may show the download domain as *.firebasestorage.app.
  // The SDK will resolve the default bucket from the project ID when omitted.
  storageBucket: "roi-website-admin.appspot.com"
};

// Initialize Firebase core services
export const app = initializeApp(firebaseConfig);
export const db = getFirestore(app);
export const storage = getStorage(app);
export const auth = getAuth(app);

// Convenience re-export for timestamps
export { serverTimestamp };

