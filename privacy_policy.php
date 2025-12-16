<?php
// Privacy Policy Page - Public Access
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Privacy Policy - DARLa HRIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.3.0/css/all.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .policy-header {
            background: linear-gradient(135deg, #198754 0%, #20c997 100%);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
        }
        .policy-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2.5rem;
            margin-bottom: 2rem;
        }
        .policy-content h2 {
            color: #198754;
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 2rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        .policy-content h2:first-child {
            margin-top: 0;
        }
        .policy-content h3 {
            color: #495057;
            font-size: 1.2rem;
            font-weight: 600;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .policy-content p {
            color: #495057;
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        .policy-content ul, .policy-content ol {
            color: #495057;
            line-height: 1.8;
            margin-bottom: 1rem;
            padding-left: 2rem;
        }
        .policy-content li {
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
        footer {
            background-color: #343a40;
            color: white;
            padding: 2rem 0;
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <div class="policy-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <img src="BG-DAR.png" alt="DAR Logo" style="max-width: 80px; height: auto; background: white; padding: 10px; border-radius: 8px;">
                </div>
                <div class="col-md-10">
                    <h1 class="mb-2"><i class="fas fa-shield-alt me-2"></i>Privacy Policy</h1>
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

        <div class="policy-content">
            <h2>1. Introduction</h2>
            <p>
                Welcome to DARLa HRIS (Human Resources Information System) operated by the Department of Agrarian Reform - La Union. 
                We are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy 
                explains how we collect, use, disclose, and safeguard your information when you use our HRIS system.
            </p>
            <p>
                By accessing and using DARLa HRIS, you acknowledge that you have read, understood, and agree to be bound by this Privacy Policy. 
                If you do not agree with our policies and practices, please do not use our system.
            </p>

            <h2>2. Information We Collect</h2>
            
            <h3>2.1 Personal Information</h3>
            <p>We collect the following types of personal information:</p>
            <ul>
                <li><strong>Employee Information:</strong> Name, employee ID, birthdate, home address, contact numbers, email address, civil status, and spouse information</li>
                <li><strong>Employment Details:</strong> BP number, Pag-ibig number, PhilHealth number, designations, employment status, and service records</li>
                <li><strong>Professional Records:</strong> Training records, leave records, appointment history, and salary information</li>
                <li><strong>Administrative Data:</strong> Username, password (encrypted), login history, and activity logs</li>
            </ul>

            <h3>2.2 Automatically Collected Information</h3>
            <p>When you access our system, we may automatically collect:</p>
            <ul>
                <li>IP address and location data</li>
                <li>Browser type and version</li>
                <li>Device information</li>
                <li>Access times and dates</li>
                <li>Pages viewed and actions taken</li>
            </ul>

            <h2>3. How We Use Your Information</h2>
            <p>We use the collected information for the following purposes:</p>
            <ul>
                <li><strong>Human Resources Management:</strong> To manage employee records, track attendance, process leave requests, and maintain employment history</li>
                <li><strong>System Administration:</strong> To manage user accounts, authenticate access, and maintain system security</li>
                <li><strong>Reporting and Analytics:</strong> To generate reports, analyze employment trends, and support decision-making</li>
                <li><strong>Compliance:</strong> To comply with legal obligations, government regulations, and audit requirements</li>
                <li><strong>Communication:</strong> To send notifications, reminders, and important updates related to your employment</li>
                <li><strong>Security:</strong> To detect and prevent fraud, unauthorized access, and other security threats</li>
            </ul>

            <h2>4. Data Protection and Security</h2>
            <p>
                We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, 
                alteration, disclosure, or destruction. These measures include:
            </p>
            <ul>
                <li>Encryption of sensitive data in transit and at rest</li>
                <li>Secure password hashing using industry-standard algorithms</li>
                <li>Regular security audits and vulnerability assessments</li>
                <li>Access controls and authentication mechanisms</li>
                <li>Regular backups of data with secure storage</li>
                <li>Activity logging and monitoring for suspicious activities</li>
                <li>Restricted access to personal information on a need-to-know basis</li>
            </ul>

            <h2>5. Data Sharing and Disclosure</h2>
            <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:</p>
            <ul>
                <li><strong>Government Agencies:</strong> When required by law or government regulations, we may disclose information to authorized government agencies</li>
                <li><strong>Legal Requirements:</strong> When required by court order, subpoena, or other legal process</li>
                <li><strong>Service Providers:</strong> With trusted third-party service providers who assist in system operations, under strict confidentiality agreements</li>
                <li><strong>Emergency Situations:</strong> When necessary to protect the rights, property, or safety of employees, the organization, or the public</li>
            </ul>

            <h2>6. Data Retention</h2>
            <p>
                We retain your personal information for as long as necessary to fulfill the purposes outlined in this Privacy Policy, unless a longer 
                retention period is required or permitted by law. Employee records are typically retained according to government retention policies 
                and may be kept for extended periods for historical and legal compliance purposes.
            </p>

            <h2>7. Your Rights</h2>
            <p>You have the following rights regarding your personal information:</p>
            <ul>
                <li><strong>Access:</strong> You have the right to request access to your personal information stored in our system</li>
                <li><strong>Correction:</strong> You may request correction of inaccurate or incomplete information</li>
                <li><strong>Update:</strong> You can update certain information through your profile or by contacting the system administrator</li>
                <li><strong>Complaint:</strong> You have the right to file a complaint if you believe your privacy rights have been violated</li>
            </ul>
            <p>
                To exercise these rights, please contact the system administrator or the Data Protection Officer of the Department of Agrarian Reform - La Union.
            </p>

            <h2>8. Cookies and Tracking Technologies</h2>
            <p>
                Our system uses session cookies to maintain your login state and improve user experience. These cookies are essential for system 
                functionality and are automatically deleted when you close your browser. We do not use tracking cookies for advertising or 
                third-party analytics.
            </p>

            <h2>9. Third-Party Links</h2>
            <p>
                Our system may contain links to external websites or services. We are not responsible for the privacy practices or content of 
                these third-party sites. We encourage you to review the privacy policies of any external sites you visit.
            </p>

            <h2>10. Children's Privacy</h2>
            <p>
                DARLa HRIS is designed for use by government employees and authorized personnel. We do not knowingly collect personal information 
                from individuals under the age of 18. If you believe we have inadvertently collected such information, please contact us immediately.
            </p>

            <h2>11. Changes to This Privacy Policy</h2>
            <p>
                We reserve the right to update or modify this Privacy Policy at any time. Any changes will be posted on this page with an updated 
                "Last Updated" date. We encourage you to review this Privacy Policy periodically to stay informed about how we protect your information. 
                Your continued use of the system after any changes constitutes acceptance of the updated Privacy Policy.
            </p>

            <h2>12. Contact Information</h2>
            <p>If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact:</p>
            <div style="background-color: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-top: 1rem;">
                <p class="mb-1"><strong>Data Protection Officer</strong></p>
                <p class="mb-1">Department of Agrarian Reform - La Union</p>
                <p class="mb-1"><i class="fas fa-envelope me-2"></i>Email: darla.hris@dar.gov.ph</p>
                <p class="mb-0"><i class="fas fa-phone me-2"></i>Phone: (072) 242-5555</p>
            </div>

            <h2>13. Compliance with Data Privacy Laws</h2>
            <p>
                This Privacy Policy is designed to comply with the Data Privacy Act of 2012 (Republic Act No. 10173) of the Philippines and other 
                applicable data protection laws and regulations. We are committed to protecting the privacy rights of all individuals whose 
                information we process.
            </p>
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

