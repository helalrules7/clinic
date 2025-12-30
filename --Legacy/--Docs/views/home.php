<article class="doc-content">
    <?php if ($currentPost): ?>
        <h1><?= htmlspecialchars($currentPost['title']) ?></h1>
        <div class="prose">
            <?= $currentPost['content'] ?>
        </div>
    <?php else: ?>
        <h1><?= $lang === 'ar' ? 'مرحباً بك في التوثيق' : 'Welcome to the Documentation' ?></h1>
        <p class="lead">
            <?= $lang === 'ar' 
                ? 'اختر موضوعاً من القائمة الجانبية أو استخدم البحث.' 
                : 'Select a topic from the sidebar or search to get started.' ?>
        </p>
    <?php endif; ?>
</article>
