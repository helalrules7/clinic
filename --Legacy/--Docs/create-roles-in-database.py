#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sqlite3
import os
import re

script_dir = '/var/www/html/clinic/--Docs'
db_path = os.path.join(script_dir, 'database.sqlite')

# Connect to database
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

# Read CSS from Getting Started
cursor.execute("SELECT content FROM translations WHERE item_id = 1 AND locale = 'en'")
getting_started_content = cursor.fetchone()[0]

css_match = re.search(r'<style>(.*?)</style>', getting_started_content, re.DOTALL)
css_content = css_match.group(1) if css_match else ""

# JavaScript
js_content = """(function() {
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
                }
            }
        });
    });
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

# Merge function
def merge_content(html_file):
    with open(html_file, 'r', encoding='utf-8') as f:
        html = f.read()
    return f"""<style>
{css_content}
</style>

{html}

<script>
//<![CDATA[
{js_content}
//]]>
</script>"""

# Content files
content_files = {
    'roles-root': {
        'title_en': 'Roles',
        'title_ar': 'الأدوار',
        'en': os.path.join(script_dir, 'content', 'roles-root-en.html'),
        'ar': os.path.join(script_dir, 'content', 'roles-root-ar.html')
    },
    'doctor-role': {
        'title_en': 'Doctor Role',
        'title_ar': 'دور الطبيب',
        'en': os.path.join(script_dir, 'content', 'doctor-role-en.html'),
        'ar': os.path.join(script_dir, 'content', 'doctor-role-ar.html')
    },
    'admin-role': {
        'title_en': 'Administrator Role',
        'title_ar': 'دور المدير',
        'en': os.path.join(script_dir, 'content', 'admin-role-en.html'),
        'ar': os.path.join(script_dir, 'content', 'admin-role-ar.html')
    },
    'secretary-role': {
        'title_en': 'Secretary Role',
        'title_ar': 'دور السكرتير',
        'en': os.path.join(script_dir, 'content', 'secretary-role-en.html'),
        'ar': os.path.join(script_dir, 'content', 'secretary-role-ar.html')
    }
}

try:
    conn.execute('BEGIN TRANSACTION')
    
    # 1. Create Root Item "Roles"
    cursor.execute("INSERT INTO items (type, sort_order) VALUES ('root', 10)")
    roles_root_id = cursor.lastrowid
    
    # Insert translations for Roles root
    merged_roles_en = merge_content(content_files['roles-root']['en'])
    merged_roles_ar = merge_content(content_files['roles-root']['ar'])
    
    cursor.execute("INSERT INTO translations (item_id, locale, title, content) VALUES (?, ?, ?, ?)",
                   (roles_root_id, 'en', content_files['roles-root']['title_en'], merged_roles_en))
    cursor.execute("INSERT INTO translations (item_id, locale, title, content) VALUES (?, ?, ?, ?)",
                   (roles_root_id, 'ar', content_files['roles-root']['title_ar'], merged_roles_ar))
    
    print(f"✓ Created Root Item 'Roles' (ID: {roles_root_id})")
    
    # 2. Create Sub Items
    sub_items = [
        ('doctor-role', 'sub'),
        ('admin-role', 'sub'),
        ('secretary-role', 'sub')
    ]
    
    created_items = {}
    for item_key, item_type in sub_items:
        cursor.execute("INSERT INTO items (parent_id, type, sort_order) VALUES (?, ?, ?)",
                       (roles_root_id, item_type, 0))
        item_id = cursor.lastrowid
        
        # Merge content
        merged_en = merge_content(content_files[item_key]['en'])
        merged_ar = merge_content(content_files[item_key]['ar'])
        
        # Insert translations
        cursor.execute("INSERT INTO translations (item_id, locale, title, content) VALUES (?, ?, ?, ?)",
                       (item_id, 'en', content_files[item_key]['title_en'], merged_en))
        cursor.execute("INSERT INTO translations (item_id, locale, title, content) VALUES (?, ?, ?, ?)",
                       (item_id, 'ar', content_files[item_key]['title_ar'], merged_ar))
        
        created_items[item_key] = item_id
        print(f"✓ Created Sub Item '{content_files[item_key]['title_en']}' (ID: {item_id})")
    
    conn.commit()
    
    # Verify
    cursor.execute("SELECT id, type, parent_id FROM items WHERE id IN (?, ?, ?, ?)",
                   (roles_root_id, created_items['doctor-role'], created_items['admin-role'], created_items['secretary-role']))
    items = cursor.fetchall()
    
    print("\n✓ Database updated successfully!")
    print("\nCreated items:")
    for item in items:
        cursor.execute("SELECT locale, title, LENGTH(content) FROM translations WHERE item_id = ?", (item[0],))
        translations = cursor.fetchall()
        print(f"\n  Item ID {item[0]} (type: {item[1]}, parent: {item[2]}):")
        for trans in translations:
            print(f"    {trans[0]}: {trans[1]} - {trans[2]} bytes")
    
    conn.close()
    
except Exception as e:
    conn.rollback()
    print(f"✗ Error: {e}")
    import traceback
    traceback.print_exc()
    exit(1)
