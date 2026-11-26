<?php $title = 'Security'; ?>
<article class="card prose">
    <h1>Security</h1>
    <h2>How accounts are protected</h2>
    <ul>
        <li>Passwords are stored only as hashes.</li>
        <li>Repeated failed sign-in attempts are rate limited.</li>
        <li>Password reset links are random, work once and expire after 30 minutes.</li>
        <li>The session ID changes at sign-in, and changing your password signs out your other sessions.</li>
        <li>Suspended or inactive accounts lose access on their next request.</li>
    </ul>
    <h2>How requests and data are protected</h2>
    <ul>
        <li>Every form submission is checked with a CSRF token.</li>
        <li>Database queries use prepared statements, and displayed text is escaped.</li>
        <li>Each page is limited to the roles allowed to use it, and ownership of records is checked.</li>
        <li>A Content Security Policy blocks inline and third-party scripts.</li>
        <li>Uploaded images are checked by their content and renamed.</li>
    </ul>
    <h2>Limits</h2>
    <p>This demonstration project has not had an independent security audit. A public deployment needs HTTPS, a supported PHP version and proper email delivery.</p>
</article>
