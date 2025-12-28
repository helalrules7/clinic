#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sqlite3
import os
import re

script_dir = os.path.dirname(os.path.abspath(__file__))
db_path = os.path.join(script_dir, 'database.sqlite')

# Read CSS (Getting Started styles only - from line 808 to 1544)
css_path = os.path.join(script_dir, 'public', 'assets', 'css', 'style.css')
# Since we removed the CSS, we need to read it from the original backup or recreate it
# For now, let's read from content files and extract CSS/JS from there

# Read JS (Getting Started scripts only)
js_path = os.path.join(script_dir, 'public', 'assets', 'js', 'app.js')
# JS was removed, so we'll recreate it from the content

# Read HTML content files
content_en_path = os.path.join(script_dir, 'content', 'getting-started-en.html')
content_ar_path = os.path.join(script_dir, 'content', 'getting-started-ar.html')

with open(content_en_path, 'r', encoding='utf-8') as f:
    html_en = f.read()

with open(content_ar_path, 'r', encoding='utf-8') as f:
    html_ar = f.read()

# Read CSS from backup or recreate - let's read the full CSS file and extract
css_full_path = '/var/www/html/clinic/--Docs/public/assets/css/style.css'
# Actually, we need to get the CSS that was removed. Let's recreate it from a known source
# For now, let's read the CSS that should be in the database already

# Read JS that should be embedded
js_content = """
    // Copy to Clipboard Functionality
    const copyButtons = document.querySelectorAll('.copy-btn');
    copyButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            const targetId = btn.getAttribute('data-copy-target');
            const codeElement = document.getElementById(targetId);
            
            if (codeElement) {
                const text = codeElement.textContent;
                try {
                    await navigator.clipboard.writeText(text);
                    
                    // Update button state
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="ri-check-line"></i> Copied!';
                    btn.classList.add('copied');
                    
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('copied');
                    }, 2000);
                } catch (err) {
                    console.error('Failed to copy:', err);
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.style.position = 'fixed';
                    textArea.style.opacity = '0';
                    document.body.appendChild(textArea);
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        btn.innerHTML = '<i class="ri-check-line"></i> Copied!';
                        setTimeout(() => {
                            btn.innerHTML = originalHTML;
                        }, 2000);
                    } catch (fallbackErr) {
                        console.error('Fallback copy failed:', fallbackErr);
                    }
                    document.body.removeChild(textArea);
                }
            }
        });
    });

    // Accordion Functionality
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    accordionHeaders.forEach(header => {
        header.addEventListener('click', () => {
            const accordionItem = header.parentElement;
            const isActive = accordionItem.classList.contains('active');
            
            // Close all accordions
            document.querySelectorAll('.accordion-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Open clicked accordion if it wasn't active
            if (!isActive) {
                accordionItem.classList.add('active');
            }
        });
    });

    // Tabs Functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetTab = btn.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            btn.classList.add('active');
            const targetContent = document.querySelector(`.tab-content[data-tab="${targetTab}"]`);
            if (targetContent) {
                targetContent.classList.add('active');
            }
        });
    });

    // Smooth Scroll for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href.length > 1) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });

    // Intersection Observer for Fade-in Animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe all content sections
    document.querySelectorAll('.content-section, .info-card, .step-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
"""

# Read CSS - we need to get it from the database or recreate
# Let's read from database first to get the current CSS
conn = sqlite3.connect(db_path)
cursor = conn.cursor()
cursor.execute("SELECT content FROM translations WHERE item_id = 1 AND locale = 'en'")
current_content = cursor.fetchone()[0]

# Extract CSS from current content
css_match = re.search(r'<style>(.*?)</style>', current_content, re.DOTALL)
if css_match:
    css_content = css_match.group(1)
else:
    # If not found, we need to recreate it - for now use empty
    css_content = ""

# Fix HTML: Ensure all < and > in code blocks are properly escaped
# But keep them as &lt; and &gt; in the HTML
def fix_html_code_blocks(html):
    # Find all <pre><code> blocks and ensure content is properly escaped
    def escape_code_content(match):
        code_content = match.group(1)
        # Replace < and > with &lt; and &gt; but keep existing &lt; and &gt;
        code_content = code_content.replace('&', '&amp;')  # First escape &
        code_content = code_content.replace('&amp;lt;', '&lt;')  # Restore &lt;
        code_content = code_content.replace('&amp;gt;', '&gt;')  # Restore &gt;
        code_content = code_content.replace('<', '&lt;')
        code_content = code_content.replace('>', '&gt;')
        code_content = code_content.replace('&amp;', '&')  # Restore &
        return f'<pre id="{match.group(2)}"><code>{code_content}</code></pre>'
    
    # Pattern to match <pre id="..."><code>...</code></pre>
    html = re.sub(r'<pre id="([^"]+)"><code>(.*?)</code></pre>', escape_code_content, html, flags=re.DOTALL)
    return html

# Fix HTML content
html_en = fix_html_code_blocks(html_en)
html_ar = fix_html_code_blocks(html_ar)

# Merge: HTML + CSS + JS
merged_en = f"""<style>
{css_content}
</style>

{html_en}

<script>
(function() {{
{js_content}
}})();
</script>"""

merged_ar = f"""<style>
{css_content}
</style>

{html_ar}

<script>
(function() {{
{js_content}
}})();
</script>"""

# Update database
cursor.execute("UPDATE translations SET content = ? WHERE item_id = 1 AND locale = 'en'", (merged_en,))
cursor.execute("UPDATE translations SET content = ? WHERE item_id = 1 AND locale = 'ar'", (merged_ar,))
conn.commit()

cursor.execute("SELECT locale, LENGTH(content) as content_length FROM translations WHERE item_id = 1")
rows = cursor.fetchall()

print("✓ Successfully fixed and merged content!")
print("\nContent sizes:")
for row in rows:
    print(f"  {row[0]}: {row[1]} bytes")

conn.close()
