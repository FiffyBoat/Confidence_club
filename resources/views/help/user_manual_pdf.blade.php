<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Confidence Club Members - User Manual</title>
    <style>
        @page { margin: 26px 32px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #1d1d1d; font-size: 12px; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        h2 { font-size: 14px; margin: 18px 0 8px; }
        h3 { font-size: 12px; margin: 12px 0 6px; }
        .subtitle { color: #6b6b6b; margin-bottom: 16px; }
        ul { margin: 6px 0 0 16px; }
        li { margin-bottom: 4px; }
        .section { margin-bottom: 12px; }
        .box {
            border: 1px solid #ead7dc;
            border-radius: 8px;
            padding: 10px 12px;
            margin-top: 6px;
            background: #fff9fb;
        }
    </style>
</head>
<body>
    <h1>Confidence Club Members - User Manual</h1>
    <div class="subtitle">Quick guide for Admin and Treasurer roles, plus public viewer access.</div>

    <div class="section">
        <h2>Quick Start</h2>
        <ul>
            <li>Admin login: /admin/login</li>
            <li>Public viewer dashboard: /viewer (no login)</li>
            <li>Public directory: /viewer/members</li>
            <li>Transparency portal: /transparency (if enabled)</li>
        </ul>
    </div>

    <div class="section">
        <h2>Roles & Access</h2>
        <div class="box">
            <ul>
                <li><strong>Admin:</strong> full access, manages users, controls public visibility.</li>
                <li><strong>Treasurer:</strong> records payments, receipts, and reports.</li>
                <li><strong>Public Visitors:</strong> no login, view-only dashboard and directory (admin-controlled sections).</li>
            </ul>
        </div>
    </div>

    <div class="section">
        <h2>Members</h2>
        <ul>
            <li>Add Member from Members -> Add Member.</li>
            <li>Uncheck "Record admission fee" if payment is not ready.</li>
            <li>Edit later to record the admission fee.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Contributions & Dues</h2>
        <ul>
            <li>Contributions -> Add (Admission Fee, Monthly Dues, Extra Levies).</li>
            <li>Dues -> Record Monthly Dues payments.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Special Contributions & Donations</h2>
        <ul>
            <li>Minimum special contribution: GHS 100.</li>
            <li>Shown on public dashboard with "Paid" and description when enabled.</li>
            <li>Donations track pooled special contributions.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Income, Expenses & Loans</h2>
        <ul>
            <li>Income -> Record other income sources.</li>
            <li>Expenses -> Record club expenses.</li>
            <li>Loans -> Track lending and repayments.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Receipts & Reports</h2>
        <ul>
            <li>Receipts auto-generate for contributions, income, and repayments.</li>
            <li>Reports provide financial summaries and exports.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Announcements, Meetings & Constitution (Admin)</h2>
        <ul>
            <li>Admin -> Announcements -> Publish updates.</li>
            <li>Admin -> Meetings -> Schedule meetings.</li>
            <li>Admin -> Settings -> Upload constitution and choose what appears on the public dashboard.</li>
        </ul>
    </div>

    <div class="section">
        <h2>Public Viewer Experience</h2>
        <ul>
            <li>/viewer shows announcements, meetings, special contributions, birthdays, and transparency snapshot (if enabled).</li>
            <li>/viewer/members lets visitors search by name, ID, phone, or email and view contacts.</li>
            <li>/constitution appears when the admin uploads a constitution.</li>
            <li>Public visibility is controlled in Admin -> Settings -> Viewer Options.</li>
        </ul>
    </div>
</body>
</html>
