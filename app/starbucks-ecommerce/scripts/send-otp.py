import smtplib
import sys
import re
import os
from email.mime.text import MIMEText
from dotenv import load_dotenv

# Load .env file
load_dotenv()

# Get email credentials from environment
EMAIL_USER = os.getenv("EMAIL_USER")
EMAIL_PASS = os.getenv("EMAIL_PASS")

# Debug log for environment values
if EMAIL_USER and EMAIL_PASS:
    print("DEBUG: Loaded credentials ->", EMAIL_USER, EMAIL_PASS[:4] + "****")
else:
    print("DEBUG: Failed to load EMAIL_USER or EMAIL_PASS from .env")

def is_valid_email(email):
    return re.match(r"^[^@]+@[^@]+\.[^@]+$", email)

def send_otp(email, otp):
    msg = MIMEText(f"Your OTP is {otp}. It expires in 5 minutes.")
    msg["Subject"] = "Starbucks OTP"
    msg["From"] = EMAIL_USER
    msg["To"] = email

    with smtplib.SMTP("smtp.gmail.com", 587) as server:
        server.starttls()
        server.login(EMAIL_USER, EMAIL_PASS)
        server.send_message(msg)
        print(f"DEBUG: Sending OTP {otp} to {email}")

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: send-otp.py <email> <otp>")
        sys.exit(1)

    email, otp = sys.argv[1], sys.argv[2]

    if not is_valid_email(email):
        print("Error: Invalid email format")
        sys.exit(1)

    try:
        send_otp(email, otp)
        sys.exit(0)
    except Exception as e:
        print("Error:", e)
        sys.exit(1)
