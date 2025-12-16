<?php
// Terms and Conditions Page - Public Access
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Terms & Conditions - DARLa HRIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.3.0/css/all.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .terms-header {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .terms-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        .terms-content h2 {
            color: #198754;
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        .terms-content h2:first-child {
            margin-top: 0;
        }
        .terms-content h3 {
            color: #495057;
            font-size: 1.2rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .terms-content p {
            color: #495057;
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        .terms-content ul, .terms-content ol {
            color: #495057;
            line-height: 1.8;
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        .terms-content li {
            margin-bottom: 0.5rem;
        }
        .last-updated {
            background-color: #e7f3ef;
            border-left: 4px solid #198754;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 5px;
        }
        .back-btn {
            margin-bottom: 1.5rem;
        }
        .important-notice {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 5px;
        }
        footer {
            background-color: #343a40;
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <div class="terms-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <img src="BG-DAR.png" alt="DAR Logo" style="max-width: 80px; height: auto; background: white; padding: 10px; border-radius: 8px;">
                </div>
                <div class="col-md-10">
                    <h1 class="mb-2"><i class="fas fa-file-contract me-2"></i>Terms & Conditions</h1>
                    <p class="mb-0 opacity-75">DARLa HRIS - Department of Agrarian Reform - La Union</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="back-btn">
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>

        <div class="last-updated">
            <strong><i class="fas fa-calendar-alt me-2"></i>Last Updated:</strong> <?= date('F d, Y') ?>
        </div>

        <div class="important-notice">
            <h5 class="mb-2"><i class="fas fa-exclamation-triangle me-2"></i>Important Notice</h5>
            <p class="mb-0">
                By accessing and using DARLa HRIS, you agree to be bound by these Terms and Conditions. 
                Please read them carefully. If you do not agree with any part of these terms, you must 
                not use this system.
            </p>
        </div>

        <div class="terms-content">
            <h2>1. Acceptance of Terms</h2>
            <p>
                These Terms and Conditions ("Terms") govern your access to and use of the DARLa HRIS (Human Resources 
                Information System) operated by the Department of Agrarian Reform - La Union ("DAR", "we", "us", or "our"). 
                By accessing, browsing, or using this system, you acknowledge that you have read, understood, and agree to 
                be bound by these Terms and all applicable laws and regulations.
            </p>
            <p>
                If you do not agree with any of these Terms, you are prohibited from using or accessing this system. 
                These Terms apply to all users of the system, including employees, administrators, and authorized personnel.
            </p>

            <h2>2. System Access and Authorization</h2>
            
            <h3>2.1 Authorized Users</h3>
            <p>Access to DARLa HRIS is restricted to:</p>
            <ul>
                <li>Authorized employees of the Department of Agrarian Reform - La Union</li>
                <li>System administrators and IT personnel</li>
                <li>Other individuals explicitly authorized by DAR management</li>
            </ul>

            <h3>2.2 User Accounts</h3>
            <ul>
                <li>You are responsible for maintaining the confidentiality of your account credentials (username and password)</li>
                <li>You must not share your account credentials with any other person</li>
                <li>You are responsible for all activities that occur under your account</li>
                <li>You must immediately notify the system administrator if you suspect unauthorized access to your account</li>
                <li>You must use a strong, unique password and change it regularly</li>
            </ul>

            <h3>2.3 Access Restrictions</h3>
            <p>You agree not to:</p>
            <ul>
                <li>Access the system using another user's credentials</li>
                <li>Attempt to gain unauthorized access to any part of the system</li>
                <li>Use automated tools, scripts, or bots to access the system without authorization</li>
                <li>Circumvent or attempt to circumvent any security measures</li>
            </ul>

            <h2>3. Acceptable Use</h2>
            
            <h3>3.1 Permitted Use</h3>
            <p>You may use DARLa HRIS only for:</p>
            <ul>
                <li>Legitimate business purposes related to human resources management</li>
                <li>Accessing and updating your own employee information</li>
                <li>Performing authorized administrative functions (for administrators)</li>
                <li>Generating reports and analytics for official purposes</li>
            </ul>

            <h3>3.2 Prohibited Activities</h3>
            <p>You must not:</p>
            <ul>
                <li>Use the system for any illegal, unauthorized, or fraudulent purpose</li>
                <li>Upload, post, or transmit any malicious code, viruses, or harmful software</li>
                <li>Attempt to interfere with, disrupt, or damage the system or its servers</li>
                <li>Access, modify, or delete data belonging to other users without authorization</li>
                <li>Use the system to harass, threaten, or harm others</li>
                <li>Violate any applicable laws, regulations, or government policies</li>
                <li>Reverse engineer, decompile, or disassemble any part of the system</li>
                <li>Copy, reproduce, or distribute system content without authorization</li>
            </ul>

            <h2>4. Data and Information</h2>
            
            <h3>4.1 Data Accuracy</h3>
            <p>
                You are responsible for ensuring that all information you provide or update in the system is accurate, 
                complete, and current. You must promptly update any information that becomes inaccurate or outdated.
            </p>

            <h3>4.2 Data Ownership</h3>
            <p>
                All data entered into DARLa HRIS remains the property of the Department of Agrarian Reform - La Union. 
                You acknowledge that you have no ownership rights to the data stored in the system.
            </p>

            <h3>4.3 Confidentiality</h3>
            <p>
                You agree to maintain the confidentiality of all information accessed through the system, including but 
                not limited to employee personal information, salary data, and other sensitive information. You must 
                not disclose such information to unauthorized parties.
            </p>

            <h2>5. Intellectual Property</h2>
            <p>
                All content, features, and functionality of DARLa HRIS, including but not limited to text, graphics, 
                logos, icons, images, software, and code, are owned by the Department of Agrarian Reform - La Union 
                or its licensors and are protected by copyright, trademark, and other intellectual property laws.
            </p>
            <p>
                You may not reproduce, distribute, modify, create derivative works of, publicly display, or otherwise 
                use any content from the system without prior written authorization.
            </p>

            <h2>6. System Availability and Modifications</h2>
            <p>
                We strive to ensure the system is available and functioning properly, but we do not guarantee uninterrupted 
                or error-free operation. The system may be temporarily unavailable due to:
            </p>
            <ul>
                <li>Scheduled maintenance and updates</li>
                <li>Technical issues or system failures</li>
                <li>Security incidents or threats</li>
                <li>Force majeure events</li>
            </ul>
            <p>
                We reserve the right to modify, suspend, or discontinue any part of the system at any time without prior 
                notice. We are not liable for any loss or inconvenience resulting from system unavailability or modifications.
            </p>

            <h2>7. Privacy and Data Protection</h2>
            <p>
                Your use of DARLa HRIS is also governed by our Privacy Policy, which explains how we collect, use, and 
                protect your personal information. By using the system, you consent to the collection and use of information 
                as described in the Privacy Policy.
            </p>
            <p>
                We are committed to protecting your privacy and complying with the Data Privacy Act of 2012 (Republic Act 
                No. 10173) and other applicable data protection laws.
            </p>

            <h2>8. User Responsibilities</h2>
            <p>You are responsible for:</p>
            <ul>
                <li>Maintaining the security and confidentiality of your account</li>
                <li>All activities that occur under your account</li>
                <li>Ensuring the accuracy of information you provide</li>
                <li>Complying with all applicable laws and regulations</li>
                <li>Using the system in a manner that does not violate the rights of others</li>
                <li>Reporting any security vulnerabilities or suspicious activities</li>
            </ul>

            <h2>9. Limitation of Liability</h2>
            <p>
                To the maximum extent permitted by law, the Department of Agrarian Reform - La Union, its officers, employees, 
                and agents shall not be liable for any direct, indirect, incidental, special, consequential, or punitive damages 
                arising from:
            </p>
            <ul>
                <li>Your use or inability to use the system</li>
                <li>Any errors or omissions in the system content</li>
                <li>Unauthorized access to or alteration of your data</li>
                <li>System downtime or interruptions</li>
                <li>Loss of data or information</li>
            </ul>

            <h2>10. Indemnification</h2>
            <p>
                You agree to indemnify, defend, and hold harmless the Department of Agrarian Reform - La Union, its officers, 
                employees, and agents from and against any claims, damages, losses, liabilities, costs, and expenses (including 
                reasonable attorney's fees) arising from:
            </p>
            <ul>
                <li>Your use or misuse of the system</li>
                <li>Your violation of these Terms</li>
                <li>Your violation of any rights of another party</li>
                <li>Any content you submit or transmit through the system</li>
            </ul>

            <h2>11. Termination</h2>
            <p>
                We reserve the right to suspend or terminate your access to DARLa HRIS at any time, with or without cause or 
                notice, for any reason, including but not limited to:
            </p>
            <ul>
                <li>Violation of these Terms</li>
                <li>Unauthorized access or use</li>
                <li>Fraudulent, illegal, or harmful activities</li>
                <li>Termination of employment or authorization</li>
                <li>Security concerns or threats</li>
            </ul>
            <p>
                Upon termination, your right to use the system will immediately cease, and we may delete or deactivate your 
                account and all related information.
            </p>

            <h2>12. Changes to Terms</h2>
            <p>
                We reserve the right to modify or update these Terms at any time. Any changes will be posted on this page with 
                an updated "Last Updated" date. Your continued use of the system after any changes constitutes acceptance of 
                the modified Terms. We encourage you to review these Terms periodically.
            </p>

            <h2>13. Governing Law</h2>
            <p>
                These Terms shall be governed by and construed in accordance with the laws of the Republic of the Philippines. 
                Any disputes arising from or relating to these Terms or your use of the system shall be subject to the exclusive 
                jurisdiction of the courts of the Philippines.
            </p>

            <h2>14. Severability</h2>
            <p>
                If any provision of these Terms is found to be invalid, illegal, or unenforceable, the remaining provisions 
                shall continue in full force and effect. The invalid provision shall be modified to the minimum extent necessary 
                to make it valid and enforceable.
            </p>

            <h2>15. Waiver</h2>
            <p>
                No waiver of any term or condition of these Terms shall be deemed a further or continuing waiver of such term 
                or condition or any other term or condition. Any failure to assert a right or provision under these Terms shall 
                not constitute a waiver of such right or provision.
            </p>

            <h2>16. Entire Agreement</h2>
            <p>
                These Terms, together with the Privacy Policy, constitute the entire agreement between you and the Department of 
                Agrarian Reform - La Union regarding your use of DARLa HRIS and supersede all prior agreements and understandings.
            </p>

            <h2>17. Contact Information</h2>
            <p>If you have any questions, concerns, or requests regarding these Terms and Conditions, please contact:</p>
            <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-top: 1rem;">
                <p class="mb-1"><strong>System Administrator</strong></p>
                <p class="mb-1">Department of Agrarian Reform - La Union</p>
                <p class="mb-1"><i class="fas fa-envelope me-2"></i>Email: darla.hris@dar.gov.ph</p>
                <p class="mb-1"><i class="fas fa-phone me-2"></i>Phone: (072) 242-5555</p>
                <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Address: DAR Provincial Office, La Union</p>
            </div>
        </div>
    </div>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> DARLa HRIS. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="privacy_policy.php" class="text-white text-decoration-none me-3">Privacy Policy</a>
                    <a href="terms_conditions.php" class="text-white text-decoration-none">Terms &amp; Conditions</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

