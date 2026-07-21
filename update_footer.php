<?php
$filepath = 'D:\OSPanel\domains\localhost\81-maktab\public\temp\css\style.css';
$css = file_get_contents($filepath);

$replacements = [
    // footer base
    ".footer {\n  background: #050b14;\n  color: #e2e8f0;" => ".footer {\n  background: var(--surface);\n  color: var(--text);",
    
    // footer logo span
    ".footer-brand .footer-logo span {\n  font-size: 20px;\n  font-weight: 800;\n  color: #fff;" => ".footer-brand .footer-logo span {\n  font-size: 20px;\n  font-weight: 800;\n  color: var(--text);",
    
    // footer desc
    ".footer-desc {\n  font-size: 14px;\n  line-height: 1.6;\n  color: #94a3b8;" => ".footer-desc {\n  font-size: 14px;\n  line-height: 1.6;\n  color: var(--muted);",
    
    // footer title
    ".footer-title {\n  font-size: 16px;\n  font-weight: 700;\n  color: #fff;" => ".footer-title {\n  font-size: 16px;\n  font-weight: 700;\n  color: var(--text);",
    
    // footer links a
    ".footer-links a {\n  color: #94a3b8;" => ".footer-links a {\n  color: var(--muted);",
    
    // footer links a hover
    ".footer-links a:hover {\n  color: #fff;" => ".footer-links a:hover {\n  color: var(--primary);",
    
    // footer contact list li
    ".footer-contact-list li {\n  display: flex;\n  gap: 12px;\n  margin-bottom: 16px;\n  font-size: 14px;\n  color: #94a3b8;" => ".footer-contact-list li {\n  display: flex;\n  gap: 12px;\n  margin-bottom: 16px;\n  font-size: 14px;\n  color: var(--muted);",
    
    // footer contact list a hover
    ".footer-contact-list a:hover {\n  color: #fff;" => ".footer-contact-list a:hover {\n  color: var(--primary);",
    
    // footer bottom border
    "border-top: 1px solid rgba(255, 255, 255, 0.12);" => "border-top: 1px solid var(--border-soft);",
    
    // footer socials
    ".social-link {\n  width: 38px;\n  height: 38px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  background: rgba(255, 255, 255, 0.05);\n  border: 1px solid rgba(255, 255, 255, 0.1);\n  border-radius: 10px;\n  color: #94a3b8;" => ".social-link {\n  width: 38px;\n  height: 38px;\n  display: flex;\n  align-items: center;\n  justify-content: center;\n  background: var(--bg);\n  border: 1px solid var(--border-soft);\n  border-radius: 10px;\n  color: var(--muted);",
    
    // footer socials hover
    ".social-link:hover {\n  background: var(--primary);\n  border-color: var(--primary);\n  color: #fff;\n  transform: translateY(-3px);\n}" => ".social-link:hover {\n  background: var(--primary);\n  border-color: var(--primary);\n  color: #ffffff;\n  transform: translateY(-3px);\n}",

    // footer cop/com p
    ".footer-cop p,\n.footer-com p {\n  font-size: 14px;\n  color: #c3d7f0;\n}" => ".footer-cop p,\n.footer-com p {\n  font-size: 14px;\n  color: var(--muted);\n}",

    // footer ul li a (older one)
    ".footer ul li a {\n  color: #cbd5e1;" => ".footer ul li a {\n  color: var(--muted);",
    ".footer ul li a:hover {\n  color: #fff;" => ".footer ul li a:hover {\n  color: var(--primary);",
    
    // nav-link dark color
    "/* PRIME LEVEL NAV-LINK */\n.nav-link {\n  color: #e2e8f0;" => "/* PRIME LEVEL NAV-LINK */\n.nav-link {\n  color: var(--text);"
];

$changesMade = 0;
foreach ($replacements as $old => $new) {
    if (strpos($css, $old) !== false) {
        $css = str_replace($old, $new, $css);
        $changesMade++;
    } else {
        echo "Could not find block starting with: " . substr($old, 0, 50) . "\n";
    }
}

file_put_contents($filepath, $css);
echo "CSS footer updated. Blocks replaced: " . $changesMade . "\n";
