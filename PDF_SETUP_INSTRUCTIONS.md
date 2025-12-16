# PDF Generation Setup Instructions

## Option 1: Using Composer (Recommended)

1. Install Composer if you haven't already: https://getcomposer.org/

2. Navigate to your project directory in terminal/command prompt:
   ```
   cd C:\xampp1\htdocs\startbootstrap-sb-admin-gh-pages
   ```

3. Install TCPDF via Composer:
   ```
   composer require tecnickcom/tcpdf
   ```

4. The PDF generation will now work automatically!

## Option 2: Manual TCPDF Installation

1. Download TCPDF from: https://tcpdf.org/

2. Extract the TCPDF folder to your project root:
   ```
   C:\xampp1\htdocs\startbootstrap-sb-admin-gh-pages\tcpdf\
   ```

3. The PDF generation script will automatically detect and use it.

## Testing

After installation, visit any employee detail page (e.g., `employee.php?id=2`) and click the "Generate PDF Report" button to test.

## Features

The generated PDF includes:
- Professional header with DARLa HRIS branding
- Complete employee personal information
- Employee information and IDs
- Appointment & Promotion History
- Training Records
- Leave Records
- Service Record Entries
- Professional formatting with tables and sections
- Page numbers and generation date

