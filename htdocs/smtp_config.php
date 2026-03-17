<?php
// -- SMTP Configuration --------------------------------------------------------
// Used by the release form to send signed PDFs via Gmail.
//
// TODO: Replace the values below with your Gmail credentials.
//
// GMAIL SETUP (one-time):
//   1. Go to https://myaccount.google.com/security
//   2. Enable 2-Step Verification if not already on
//   3. Search "App passwords" ? create one ? select "Mail" / "Other"
//   4. Copy the 16-character password (no spaces) and paste below as SMTP_PASS
//   5. Set SMTP_USER to your full Gmail address
//
// Using taylorhelmantattoo@gmail.com is recommended so sent mail
// comes from a recognisable address.
// -----------------------------------------------------------------------------

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'taylorhelmantattoo@gmail.com');   // TODO: confirm this is correct
define('SMTP_PASS', 'lqqg hxjp guzc uwhy');           // TODO: replace with Gmail App Password
define('SMTP_FROM', 'taylorhelmantattoo@gmail.com');
define('SMTP_NAME', 'Taylor Helman Tattoo');

// ── Two-step form security ────────────────────────────────────────────────────
// ARTIST_PIN: the PIN Taylor enters to access the artist section
// Change this to any numeric or alphanumeric value you want
define('ARTIST_PIN',    '1182');   // TODO: change to a private PIN before going live

// TOKEN_SECRET: used to sign client links — keep this long, random, and secret
// To regenerate: pick any long random string (40+ chars)
define('TOKEN_SECRET',  'm490CXrwXo5l13GF2IWkAjcvyBd8gCdiFSWyz3iH63Q5SM50Zs');

// Twilio SMS — sign up free at twilio.com, then fill these in
define('TWILIO_SID',   'AC327926671b14c406446923cd16b0af14');   // Account SID  (ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx)
define('TWILIO_TOKEN', '10ffcff30d0388c40740bf55c8a71492');   // Auth Token
define('TWILIO_FROM',  '+16562312877');   // Your Twilio number  e.g. +15551234567
