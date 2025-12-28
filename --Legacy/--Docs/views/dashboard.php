<div class="dashboard-container">
    <h1><?= $editingPost ? 'Edit Documentation' : 'Create Documentation' ?></h1>
    
    <div class="card">
        <form id="create-post-form">
            <?php if ($editingPost): ?>
                <input type="hidden" name="id" value="<?= $editingPost['id'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Type</label>
                <select name="type" id="type-select" onchange="toggleParentSelect()">
                    <option value="root" <?= ($editingPost['type'] ?? '') === 'root' ? 'selected' : '' ?>>Root Item (Concept)</option>
                    <option value="sub" <?= ($editingPost['type'] ?? '') === 'sub' ? 'selected' : '' ?>>Sub Item (Page)</option>
                </select>
            </div>
            
            <div class="form-group" id="parent-group" style="<?= ($editingPost['type'] ?? 'root') === 'sub' ? 'display: block;' : 'display: none;' ?>">
                <label>Parent Root Item</label>
                <select name="parent_id">
                    <option value="">Select Parent...</option>
                    <?php foreach ($existingItems as $root): ?>
                        <option value="<?= $root['id'] ?>" <?= ($editingPost['parent_id'] ?? '') == $root['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($root['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid-2">
                <div class="lang-section">
                    <h3>English</h3>
                    <div class="media-help">
                        <small><strong>Insert Video (No Controls):</strong> <code>&lt;video src="URL"&gt;&lt;/video&gt;</code></small><br>
                        <small><strong>Insert Audio (With Controls):</strong> <code>&lt;audio controls src="URL"&gt;&lt;/audio&gt;</code></small>
                    </div>
                    <div class="form-group">
                        <label>Title (EN)</label>
                        <input type="text" name="title_en" required value="<?= htmlspecialchars($editingPost['translations']['en']['title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Content (EN) (HTML Supported)</label>
                        <textarea name="content_en" rows="10"><?= htmlspecialchars($editingPost['translations']['en']['content'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="lang-section">
                    <h3>Arabic</h3>
                    <div class="form-group">
                        <label>Title (AR)</label>
                        <input type="text" name="title_ar" dir="rtl" placeholder="العنوان بالعربية" value="<?= htmlspecialchars($editingPost['translations']['ar']['title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Content (AR)</label>
                        <textarea name="content_ar" rows="10" dir="rtl" placeholder="المحتوى بالعربية"><?= htmlspecialchars($editingPost['translations']['ar']['content'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

                </div>
            </div>

            <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn-primary" style="flex: 1;">
                    <?= $editingPost ? 'Update Doc' : 'Create Doc' ?>
                </button>
                
                <?php if ($editingPost): ?>
                <button type="button" id="delete-btn" class="btn-danger">
                    Delete Item
                </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
function toggleParentSelect() {
    const type = document.getElementById('type-select').value;
    const parentGroup = document.getElementById('parent-group');
    parentGroup.style.display = type === 'sub' ? 'block' : 'none';
}

<?php if ($editingPost): ?>
document.getElementById('delete-btn').addEventListener('click', async () => {
    if (!confirm('Are you sure you want to delete this item? This cannot be undone.')) return;
    
    try {
        const res = await fetch('/docs/opth/api/delete', {
            method: 'POST',
            body: JSON.stringify({ id: <?= $editingPost['id'] ?> }),
            headers: {'Content-Type': 'application/json'}
        });
        const json = await res.json();
        if (json.success) {
            alert('Deleted successfully');
            window.location.href = '/docs/opth/dashboard';
        } else {
            alert('Error deleting: ' + (json.error || 'Unknown error'));
        }
    } catch (e) {
        alert('Failed to send request');
    }
});
<?php endif; ?>

document.getElementById('create-post-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const res = await fetch('/docs/opth/api/posts', {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {'Content-Type': 'application/json'}
        });
        const json = await res.json();
        if (json.success) {
            alert(json.action === 'updated' ? 'Updated successfully!' : 'Created successfully!');
            // Redirect to dashboard root to refresh sidebar
            window.location.href = '/docs/opth/dashboard?id=' + json.id;
        } else {
            alert('Error: ' + json.error);
        }
    } catch (err) {
        alert('Failed to send request');
    }
});
</script>

<style>
.dashboard-container { max-width: 900px; margin: 0 auto; }
.card { background: var(--bg-card); padding: 2rem; border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border-color); }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
.form-group input, .form-group select, .form-group textarea {
    width: 100%; padding: 0.75rem; border-radius: 6px; border: 1px solid var(--border-color);
    background: var(--bg-body); color: var(--text-main); font-family: inherit; font-size: 1rem;
}
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
@media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
.btn-danger {
    background: #ef4444; color: white; border: none; padding: 0.6rem 1.5rem;
    border-radius: 50px; font-weight: 700; cursor: pointer;
}
.btn-danger:hover { filter: brightness(1.1); }
</style>
