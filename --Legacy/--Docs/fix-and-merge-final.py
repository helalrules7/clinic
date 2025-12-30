#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sqlite3
import os
import html

script_dir = os.path.dirname(os.path.abspath(__file__))
db_path = os.path.join(script_dir, 'database.sqlite')

# Read HTML content files
content_en_path = os.path.join(script_dir, 'content', 'getting-started-en.html')
content_ar_path = os.path.join(script_dir, 'content', 'getting-started-ar.html')

with open(content_en_path, 'r', encoding='utf-8') as f:
    html_en = f.read()

with open(content_ar_path, 'r', encoding='utf-8') as f:
    html_ar = f.read()

# Read CSS from database (extract it)
conn = sqlite3.connect(db_path)
cursor = conn.cursor()
cursor.execute("SELECT content FROM translations WHERE item_id = 1 AND locale = 'en'")
current_content = cursor.fetchone()[0]

import re
css_match = re.search(r'<style>(.*?)</style>', current_content, re.DOTALL)
css_content = css_match.group(1) if css_match else ""

# JavaScript content - wrapped in IIFE to avoid conflicts
js_content = """(function() {
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
                    const originalHTML = btn.innerHTML;
                    btn.innerHTML = '<i class="ri-check-line"></i> Copied!';
                    btn.classList.add('copied');
                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.classList.remove('copied');
                    }, 2000);
                } catch (err) {
                    console.error('Failed to copy:', err);
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
                            btn.innerHTML = btn.getAttribute('data-original-html') || '<i class="ri-file-copy-line"></i> Copy';
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
            document.querySelectorAll('.accordion-item').forEach(item => {
                item.classList.remove('active');
            });
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
            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            const targetContent = document.querySelector('.tab-content[data-tab="' + targetTab + '"]');
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
    document.querySelectorAll('.content-section, .info-card, .step-item').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
})();"""

# Merge: HTML + CSS + JS
# Use CDATA for script to avoid parsing issues
merged_en = f"""<style>
{css_content}
</style>

{html_en}

<script>
//<![CDATA[
{js_content}
//]]>
</script>"""

merged_ar = f"""<style>
{css_content}
</style>

{html_ar}

<script>
//<![CDATA[
{js_content}
//]]>
</script>"""

# Update database
cursor.execute("UPDATE translations SET content = ? WHERE item_id = 1 AND locale = 'en'", (merged_en,))
cursor.execute("UPDATE translations SET content = ? WHERE item_id = 1 AND locale = 'ar'", (merged_ar,))
conn.commit()

cursor.execute("SELECT locale, LENGTH(content) as content_length FROM translations WHERE item_id = 1")
rows = cursor.fetchall()

print("✓ Successfully fixed and merged content with CDATA protection!")
print("\nContent sizes:")
for row in rows:
    print(f"  {row[0]}: {row[1]} bytes")

conn.close()
