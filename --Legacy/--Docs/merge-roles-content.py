#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sqlite3
import os
import re

script_dir = os.path.dirname(os.path.abspath(__file__))
db_path = os.path.join(script_dir, 'database.sqlite')

# Read CSS from Getting Started content in database (extract it)
conn = sqlite3.connect(db_path)
cursor = conn.cursor()
cursor.execute("SELECT content FROM translations WHERE item_id = 1 AND locale = 'en'")
getting_started_content = cursor.fetchone()[0]

# Extract CSS from Getting Started
css_match = re.search(r'<style>(.*?)</style>', getting_started_content, re.DOTALL)
css_content = css_match.group(1) if css_match else ""

# JavaScript content - wrapped in IIFE
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

# Function to merge content with CSS and JS
def merge_content(html_file):
    with open(html_file, 'r', encoding='utf-8') as f:
        html = f.read()
    
    merged = f"""<style>
{css_content}
</style>

{html}

<script>
//<![CDATA[
{js_content}
//]]>
</script>"""
    
    return merged

# Content files to process
content_files = {
    'roles-root': {
        'en': os.path.join(script_dir, 'content', 'roles-root-en.html'),
        'ar': os.path.join(script_dir, 'content', 'roles-root-ar.html')
    },
    'doctor-role': {
        'en': os.path.join(script_dir, 'content', 'doctor-role-en.html'),
        'ar': os.path.join(script_dir, 'content', 'doctor-role-ar.html')
    },
    'admin-role': {
        'en': os.path.join(script_dir, 'content', 'admin-role-en.html'),
        'ar': os.path.join(script_dir, 'content', 'admin-role-ar.html')
    },
    'secretary-role': {
        'en': os.path.join(script_dir, 'content', 'secretary-role-en.html'),
        'ar': os.path.join(script_dir, 'content', 'secretary-role-ar.html')
    }
}

# Merge all content files
merged_content = {}
for key, files in content_files.items():
    merged_content[key] = {
        'en': merge_content(files['en']),
        'ar': merge_content(files['ar'])
    }

print("✓ All content files merged with CSS and JS!")
print(f"  - Processed {len(content_files)} content sets")
print(f"  - Each with English and Arabic versions")
print("\nReady to update database...")

# Store merged content for database update
conn.close()

# Save merged content to a variable for later use
import json
with open('/tmp/merged_roles_content.json', 'w', encoding='utf-8') as f:
    json.dump(merged_content, f, ensure_ascii=False, indent=2)

print("\n✓ Merged content saved to /tmp/merged_roles_content.json")
