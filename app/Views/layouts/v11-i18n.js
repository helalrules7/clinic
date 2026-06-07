/**
 * v11 UI i18n — JS surfaces (todo, notes, cmdk, keyboard-help, actions-registry).
 * Secretary layout: lang="ar" + data-layout="secretary".
 */
(function () {
    'use strict';

    var AR = {
        'error.title': 'خطأ',
        'error.generic': 'حدث خطأ ما.',

        'modal.confirm_title': 'يرجى التأكيد',
        'modal.confirm_msg': 'هل أنت متأكد؟',
        'modal.cancel': 'إلغاء',
        'modal.confirm': 'تأكيد',
        'modal.ok': 'حسناً',
        'modal.close': 'إغلاق',
        'modal.notice': 'تنبيه',
        'modal.delete': 'حذف',

        'todo.open_drawer': 'فتح درج المهام',
        'todo.new_list': 'قائمة جديدة',
        'todo.list_default': 'قائمة',
        'todo.progress.lets_go': 'لنبدأ!',
        'todo.progress.nice_start': 'بداية جيدة',
        'todo.progress.keep_up': 'استمر!',
        'todo.progress.almost': 'أوشكت على الانتهاء!',
        'todo.progress.all_done': 'أنجزت كل شيء!',
        'todo.progress.sub': '{done} من {total} مكتملة',
        'todo.delete': 'حذف',
        'todo.delete_task': 'حذف المهمة',
        'todo.delete_task_msg': 'هل تريد حذف هذه المهمة؟',
        'todo.delete_list_perm_msg': 'حذف القائمة «{name}» وجميع مهامها نهائياً؟ لا يمكن التراجع عن هذا الإجراء.',
        'todo.archive_btn': 'أرشفة',
        'todo.new_task': 'مهمة جديدة',
        'todo.edit_task': 'تعديل مهمة',
        'todo.new_list_modal': 'قائمة جديدة',
        'todo.edit_list_modal': 'تعديل قائمة',
        'todo.delete_list': 'حذف القائمة',
        'todo.archive_list': 'أرشفة القائمة',
        'todo.delete_list_msg': 'حذف القائمة وجميع مهامها؟',
        'todo.archive_list_msg': 'أرشفة هذه القائمة؟',
        'todo.toast_title': '{n} مهمة تحتاج انتباهك',
        'todo.toast_body': 'افتح درج المهام لمراجعتها.',
        'todo.dismiss': 'تجاهل',
        'todo.priority.low': 'منخفضة',
        'todo.priority.med': 'متوسطة',
        'todo.priority.high': 'عالية',
        'todo.due.overdue': 'متأخرة',
        'todo.due.today': 'اليوم',
        'todo.due.tomorrow': 'غداً',
        'todo.archived_count': '{n} مهمة',
        'todo.create_list': 'إنشاء قائمة',
        'todo.save_list': 'حفظ القائمة',
        'todo.cancel': 'إلغاء',

        'notes.open_drawer': 'فتح الملاحظات',
        'notes.couldnt_load': 'تعذّر تحميل الملاحظات.',
        'notes.retry': 'إعادة المحاولة',
        'notes.no_match': 'لا توجد ملاحظات مطابقة.',
        'notes.no_notes': 'لا توجد ملاحظات بعد.',
        'notes.empty_sub': 'دوّن شيئاً أعلاه للبدء.',
        'notes.untitled': '(بدون عنوان)',
        'notes.from_board': 'من لوحة الملاحظات',
        'notes.delete_note': 'حذف الملاحظة',
        'notes.delete': 'حذف',
        'notes.delete_msg': 'حذف هذه الملاحظة نهائياً؟',
        'notes.cancel': 'إلغاء',

        'quicknote.delete_title': 'حذف الملاحظة؟',
        'quicknote.delete_msg': 'حذف «{title}» نهائياً؟ لا يمكن التراجع عن هذا الإجراء.',
        'quicknote.untitled': 'ملاحظة بدون عنوان',
        'note.delete_title': 'حذف الملاحظة',
        'note.delete_msg': 'ستُحذف هذه الملاحظة السريعة نهائياً.',

        'board.delete_note_title': 'حذف الملاحظة؟',
        'board.delete_note_msg': 'ستُزال ملاحظة اللوحة. لا يمكن التراجع عن هذا الإجراء.',

        'template.delete_title': 'حذف القالب؟',
        'template.delete_msg': 'سيُزال «{title}» نهائياً.',
        'template.untitled': 'بدون عنوان',

        'boardmgr.delete_title': 'حذف اللوحة',
        'boardmgr.error_title': 'خطأ',

        'patient.delete_note_title': 'حذف الملاحظة؟',
        'patient.delete_note_msg': 'ستُزال ملاحظة اللوحة. لا يمكن التراجع عن هذا الإجراء.',
        'notes.new_note': 'ملاحظة جديدة',
        'notes.edit_note': 'تعديل ملاحظة',
        'notes.time.just_now': 'الآن',
        'notes.time.min': 'منذ {n} د',
        'notes.time.hr': 'منذ {n} س',
        'notes.time.today': 'اليوم',
        'notes.time.yesterday': 'أمس',

        'cmdk.open': 'فتح لوحة الأوامر ({key})',
        'cmdk.section.patients': 'المرضى',
        'cmdk.section.pages': 'الصفحات',
        'cmdk.section.actions': 'الإجراءات',
        'cmdk.section.todos': 'المهام',
        'cmdk.error.title': 'تعذّر الوصول للبحث',
        'cmdk.error.sub': 'تحقق من الاتصال وحاول مجدداً.',
        'cmdk.no_results': 'لا نتائج لـ «{q}»',
        'cmdk.no_results.sub': 'جرّب بحثاً أو نطاقاً مختلفاً.',
        'cmdk.smart.title': 'إجراءات ذكية',
        'cmdk.smart.intro': 'نصيحة: اكتب رقم هاتف لحجز أقرب موعد، أو اسماً للبحث عن مريض.',
        'cmdk.smart.none': 'لا توجد إجراءات ذكية.',
        'cmdk.book_phone': 'حجز أقرب موعد لـ {phone}',
        'cmdk.book_phone.sub': 'البحث عن أقرب موعد متاح',

        'kbd.open_help': 'اختصارات لوحة المفاتيح',
        'kbd.open_help_title': 'اختصارات لوحة المفاتيح (?)',

        'palette.toggle': 'لوحة الألوان',

        'action.new-patient.label': 'مريض جديد',
        'action.new-patient.sub': 'إنشاء سجل مريض',
        'action.new-booking.label': 'حجز جديد',
        'action.new-booking.sub': 'فتح نافذة إضافة موعد',
        'action.new-note.label': 'ملاحظة سريعة',
        'action.new-note.sub': 'فتح نافذة الملاحظة السريعة',
        'action.new-todo.label': 'مهمة جديدة',
        'action.new-todo.sub': 'فتح درج المهام',
        'action.new-alert.label': 'تنبيه جديد',
        'action.new-alert.sub': 'إنشاء تنبيه لمريض',
        'action.notes-drawer.label': 'فتح الملاحظات',
        'action.notes-drawer.sub': 'فتح درج الملاحظات',
        'action.calendar.label': 'فتح التقويم',
        'action.calendar.sub': 'الذهاب إلى التقويم',
        'action.boards.label': 'فتح اللوحات',
        'action.boards.sub': 'الذهاب إلى اللوحات',
        'action.focus-mode.label': 'وضع التركيز',
        'action.focus-mode.sub': 'إخفاء الواجهة والتركيز',
        'action.theme-picker.label': 'اختيار السمة',
        'action.theme-picker.sub': 'تغيير اللون والوضع',
        'action.keyboard-help.label': 'اختصارات لوحة المفاتيح',
        'action.keyboard-help.sub': 'عرض كل الاختصارات',
        'action.book-by-phone.label': 'حجز أقرب موعد بالهاتف',
        'action.book-by-phone.sub': 'أقرب موعد متاح لرقم الهاتف',
        'action.book-by-phone.usage': 'اكتب رقم هاتف في اللوحة ثم اختر «حجز أقرب موعد».',
        'action.go-to-today.label': 'الذهاب لليوم',
        'action.go-to-today.sub': 'فتح التقويم على اليوم',
        'action.go-to-today.usage': 'الانتقال مباشرة لتقويم اليوم.',
        'action.daily-closure.label': 'إغلاق يومي',
        'action.daily-closure.sub': 'فتح صفحة الإغلاق اليومي',
        'action.daily-closure.usage': 'فتح إغلاق اليوم النقدي/الزيارات.',
        'action.reports.label': 'التقارير',
        'action.reports.sub': 'فتح التقارير والإحصائيات',
        'action.reports.usage': 'فتح لوحة التقارير.',
        'action.payments.label': 'المدفوعات',
        'action.payments.sub': 'فتح المدفوعات والفواتير',
        'action.payments.usage': 'فتح المدفوعات والفواتير المعلّقة.'
    };

    function isAr() {
        var html = document.documentElement;
        return html.getAttribute('lang') === 'ar'
            || html.getAttribute('data-layout') === 'secretary'
            || html.getAttribute('data-notif-lang') === 'ar';
    }

    function t(key, fallback, vars) {
        var str = (isAr() && AR[key]) ? AR[key] : (fallback != null ? fallback : key);
        if (vars && typeof str === 'string') {
            Object.keys(vars).forEach(function (k) {
                str = str.replace(new RegExp('\\{' + k + '\\}', 'g'), String(vars[k]));
            });
        }
        return str;
    }

    function actionText(id, field, en) {
        var key = 'action.' + id + '.' + field;
        return t(key, en);
    }

    window.V11I18n = {
        isAr: isAr,
        t: t,
        actionText: actionText,
        AR: AR
    };
})();
