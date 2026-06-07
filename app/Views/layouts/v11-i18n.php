<?php
/**
 * v11 UI strings — PHP templates (todo / notes / cmdk / keyboard-help).
 * Secretary layout sets $v11Lang = 'ar' before including feature surfaces.
 */
if (!function_exists('v11t')) {
    function v11_lang(): string
    {
        return ($GLOBALS['v11Lang'] ?? 'en') === 'ar' ? 'ar' : 'en';
    }

    function v11t(string $key, ?string $fallback = null): string
    {
        static $dict = null;
        if ($dict === null) {
            $dict = [
                'en' => [],
                'ar' => [
                    // —— To-Do drawer ——
                    'todo.drawer_label'       => 'درج المهام',
                    'todo.title'              => 'المهام',
                    'todo.archived_lists'     => 'قوائم مؤرشفة',
                    'todo.close'              => 'إغلاق درج المهام',
                    'todo.filter_tasks'       => 'تصفية المهام',
                    'todo.filter.open'        => 'مفتوحة',
                    'todo.filter.done'        => 'منجزة',
                    'todo.filter.all'         => 'الكل',
                    'todo.lists_nav'          => 'قوائم المهام',
                    'todo.progress_label'     => 'تقدم اليوم',
                    'todo.progress.lets_go'   => 'لنبدأ!',
                    'todo.progress_pct'       => 'نسبة الإنجاز',
                    'todo.quick_add_ph'       => 'أضف مهمة سريعة واضغط Enter…',
                    'todo.quick_add'          => 'إضافة مهمة سريعة',
                    'todo.add_task_btn'       => 'إضافة مهمة',
                    'todo.empty_title'        => 'لا شيء هنا بعد',
                    'todo.empty_sub'          => 'أضف مهمتك الأولى أعلاه للبدء.',
                    'todo.loading'            => 'جاري التحميل…',
                    'todo.add_detailed'       => 'إضافة مهمة مفصّلة',
                    'todo.rename'             => 'إعادة تسمية',
                    'todo.color'              => 'اللون',
                    'todo.move_up'            => 'تحريك لأعلى',
                    'todo.move_down'          => 'تحريك لأسفل',
                    'todo.archive_list'       => 'أرشفة القائمة',
                    'todo.delete_list'        => 'حذف القائمة',
                    'todo.new_task'           => 'مهمة جديدة',
                    'todo.edit_task'          => 'تعديل مهمة',
                    'todo.modal_close'        => 'إغلاق',
                    'todo.field.list'         => 'القائمة',
                    'todo.field.title'        => 'العنوان',
                    'todo.field.title_ph'     => 'ما الذي يجب إنجازه؟',
                    'todo.field.desc'         => 'الوصف',
                    'todo.field.desc_ph'      => 'ملاحظات، روابط، سياق…',
                    'todo.field.due'          => 'الاستحقاق',
                    'todo.field.remind'       => 'تذكير',
                    'todo.remind.none'        => 'بدون تذكير',
                    'todo.remind.15'          => 'قبل 15 دقيقة',
                    'todo.remind.60'          => 'قبل ساعة',
                    'todo.remind.240'         => 'قبل 4 ساعات',
                    'todo.remind.1440'        => 'قبل يوم',
                    'todo.field.patient'      => 'المريض',
                    'todo.field.patient_ph'   => 'ابحث بالاسم أو الهاتف…',
                    'todo.field.priority'     => 'الأولوية',
                    'todo.priority.low'       => 'منخفضة',
                    'todo.priority.med'       => 'متوسطة',
                    'todo.priority.high'      => 'عالية',
                    'todo.cancel'             => 'إلغاء',
                    'todo.save_task'          => 'حفظ المهمة',
                    'todo.new_list'           => 'قائمة جديدة',
                    'todo.edit_list'          => 'تعديل قائمة',
                    'todo.list_name'          => 'اسم القائمة',
                    'todo.list_name_ph'       => 'مثال: متابعات العيادة',
                    'todo.list_color'         => 'اللون',
                    'todo.list_icon'          => 'الأيقونة',
                    'todo.icon.list'          => 'قائمة',
                    'todo.icon.work'          => 'عمل',
                    'todo.icon.personal'      => 'شخصي',
                    'todo.icon.clinic'        => 'عيادة',
                    'todo.icon.shopping'      => 'تسوق',
                    'todo.icon.study'         => 'دراسة',
                    'todo.icon.ideas'         => 'أفكار',
                    'todo.icon.goals'         => 'أهداف',
                    'todo.create_list'        => 'إنشاء قائمة',
                    'todo.save_list'          => 'حفظ القائمة',
                    'todo.delete_list_perm_msg' => 'حذف القائمة «{name}» وجميع مهامها نهائياً؟ لا يمكن التراجع عن هذا الإجراء.',
                    'todo.archive_btn'        => 'أرشفة',

                    'modal.confirm_title'     => 'يرجى التأكيد',
                    'modal.confirm_msg'       => 'هل أنت متأكد؟',
                    'modal.cancel'            => 'إلغاء',
                    'modal.confirm'           => 'تأكيد',
                    'modal.ok'                => 'حسناً',
                    'modal.close'             => 'إغلاق',
                    'modal.notice'            => 'تنبيه',
                    'modal.delete'            => 'حذف',
                    'todo.archived_title'     => 'قوائم مؤرشفة',
                    'todo.no_archived'        => 'لا توجد قوائم مؤرشفة',
                    'todo.restore'            => 'استعادة',
                    'todo.delete_perm'        => 'حذف نهائي',
                    'todo.open_tasks'         => 'مهام مفتوحة',
                    'todo.list_options'       => 'خيارات القائمة',
                    'todo.toggle_complete'    => 'تبديل الإنجاز',
                    'todo.snooze'             => 'تأجيل',
                    'todo.edit'               => 'تعديل',
                    'todo.delete'             => 'حذف',
                    'todo.snooze.15'          => '15 دقيقة',
                    'todo.snooze.60'          => 'ساعة',
                    'todo.snooze.240'         => '4 ساعات',
                    'todo.snooze.1440'        => 'غداً',
                    'todo.snooze.10080'       => 'الأسبوع القادم',

                    // —— Notes drawer ——
                    'notes.title'             => 'الملاحظات',
                    'notes.close'             => 'إغلاق الملاحظات',
                    'notes.filter'            => 'تصفية الملاحظات',
                    'notes.filter.all'        => 'الكل',
                    'notes.filter.pinned'     => 'مثبّتة',
                    'notes.filter.recent'     => 'حديثة',
                    'notes.search_ph'         => 'ابحث في الملاحظات…',
                    'notes.clear_search'      => 'مسح البحث',
                    'notes.quick_ph'          => 'دوّن ملاحظة سريعة… (⌘+Enter للحفظ)',
                    'notes.save'              => 'حفظ الملاحظة',
                    'notes.fab'               => 'ملاحظة جديدة بخيارات كاملة',
                    'notes.new_note'          => 'ملاحظة جديدة',
                    'notes.edit_note'         => 'تعديل ملاحظة',
                    'notes.field.title'       => 'العنوان (اختياري)',
                    'notes.field.title_ph'    => 'عنوان قصير',
                    'notes.field.body'        => 'المحتوى',
                    'notes.field.body_ph'     => 'اكتب ملاحظتك…',
                    'notes.field.bg'          => 'الخلفية',
                    'notes.pin_top'           => 'تثبيت في الأعلى',
                    'notes.cancel'            => 'إلغاء',
                    'notes.save_btn'          => 'حفظ',
                    'notes.pin_toggle'        => 'تثبيت / إلغاء التثبيت',
                    'notes.edit'              => 'تعديل',
                    'notes.delete'            => 'حذف',

                    // —— Command palette ——
                    'cmdk.title'              => 'لوحة الأوامر',
                    'cmdk.placeholder'        => 'ابحث: مرضى، صفحات، إجراءات، مهام…',
                    'cmdk.search'             => 'بحث',
                    'cmdk.smart_help'         => 'مساعدة الإجراءات الذكية',
                    'cmdk.close'              => 'إغلاق لوحة الأوامر',
                    'cmdk.filter'             => 'تصفية النتائج',
                    'cmdk.tab.all'            => 'الكل',
                    'cmdk.tab.patients'       => 'المرضى',
                    'cmdk.tab.pages'          => 'الصفحات',
                    'cmdk.tab.actions'        => 'الإجراءات',
                    'cmdk.tab.todos'          => 'المهام',
                    'cmdk.results'            => 'نتائج البحث',
                    'cmdk.empty_title'        => 'ابدأ الكتابة للبحث',
                    'cmdk.empty_sub'          => 'المرضى، الصفحات، الإجراءات والمهام.',
                    'cmdk.hint.navigate'      => 'تنقّل',
                    'cmdk.hint.select'        => 'اختيار',
                    'cmdk.hint.close'         => 'إغلاق',

                    // —— Keyboard help ——
                    'kbd.title'               => 'اختصارات لوحة المفاتيح',
                    'kbd.subtitle'            => 'اضغط <kbd class="kbd">?</kbd> في أي وقت لإعادة فتح هذه اللوحة.',
                    'kbd.close'               => 'إغلاق الاختصارات',
                    'kbd.section.global'      => 'عام',
                    'kbd.section.nav'         => 'تنقّل',
                    'kbd.section.actions'     => 'إجراءات',
                    'kbd.section.notif'       => 'الإشعارات',
                    'kbd.open_palette'        => 'فتح لوحة الأوامر',
                    'kbd.show_help'           => 'عرض هذه المساعدة',
                    'kbd.esc_close'           => 'إغلاق نافذة أو درج أو لوحة',
                    'kbd.then'                => 'ثم',
                    'kbd.go_dashboard'        => 'الذهاب إلى لوحة التحكم',
                    'kbd.go_calendar'         => 'الذهاب إلى التقويم',
                    'kbd.go_bookings'         => 'الذهاب إلى الحجوزات',
                    'kbd.go_boards'           => 'الذهاب إلى اللوحات',
                    'kbd.go_patients'         => 'الذهاب إلى المرضى',
                    'kbd.go_payments'         => 'الذهاب إلى المدفوعات',
                    'kbd.go_settings'         => 'الذهاب إلى الإعدادات',
                    'kbd.go_profile'          => 'الذهاب إلى الملف الشخصي',
                    'kbd.new_patient'         => 'مريض جديد',
                    'kbd.open_todo'           => 'فتح درج المهام',
                    'kbd.new_alert'           => 'تنبيه جديد',
                    'kbd.focus_mode'          => 'تبديل وضع التركيز',
                    'kbd.focus_hint'          => '(صفحة تعديل الاستشارة)',
                    'kbd.notif_nav'           => 'التالي / السابق',
                    'kbd.notif_snooze'        => 'تأجيل الإشعار المحدّد',
                    'kbd.notif_pin'           => 'تثبيت الإشعار المحدّد',
                    'kbd.notif_note'          => 'تعمل هذه الاختصارات فقط عندما تكون لوحة الإشعارات مفتوحة.',
                    'kbd.footer_tip'          => 'تلميح: الاختصارات تُتجاهل أثناء الكتابة في الحقول.',
                    'kbd.close_btn'           => 'إغلاق',
                ],
            ];
        }
        $lang = v11_lang();
        if (isset($dict[$lang][$key])) {
            return $dict[$lang][$key];
        }
        if ($fallback !== null) {
            return $fallback;
        }
        if (isset($dict['en'][$key])) {
            return $dict['en'][$key];
        }
        return $key;
    }

    /** English default + Arabic when $v11Lang === 'ar'. */
    function v11e(string $key, string $en): string
    {
        return v11_lang() === 'ar' ? v11t($key, $en) : $en;
    }
}
