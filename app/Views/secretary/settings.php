<?php
/**
 * Secretary Settings — appearance & personal preferences (Arabic).
 */
?>
<link href="/app/Views/doctor/assets/css/settings.css?v=<?= file_exists(__DIR__ . '/../doctor/assets/css/settings.css') ? filemtime(__DIR__ . '/../doctor/assets/css/settings.css') : time() ?>" rel="stylesheet">

<div class="container-fluid" dir="rtl">
    <div class="row">
    <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="settings-page-header">
                        <span class="settings-page-icon"><i class="bi bi-sliders"></i></span>
                        <div>
                            <h5 class="settings-page-title arabic-text">إعدادات الواجهة</h5>
                            <p class="settings-page-sub arabic-text">تخصيص المظهر والتفضيلات الشخصية لواجهة السكرتارية</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="settings-section">
                        <h5 class="arabic-text"><i class="bi bi-palette-fill me-2"></i>المظهر</h5>
                        <div class="form-text mb-3 arabic-text" style="margin-top:-6px">
                            اختر لوحة الألوان وطريقة تبديل الوضع الداكن والفاتح. تُحفظ تفضيلاتك على كل الأجهزة.
                        </div>

                        <!-- Palette grid -->
                        <div class="setting-item">
                            <div>
                                <label class="form-label mb-0 arabic-text">لوحة الألوان</label>
                                <div class="form-text mb-2 arabic-text">ست لوحات مسمّاة — اضغط أيّاً منها للتبديل فوراً.</div>
                                <div class="appearance-grid" id="secAppearanceGrid">
                                    <?php
                                      $__paletteRows = [
                                          ['indigo',  'نيلي',    'أزرق-بنفسجي هادئ'],
                                          ['emerald', 'زمردي',   'أخضر منعش'],
                                          ['rose',    'وردي',    'وردي دافئ'],
                                          ['slate',   'رمادي',   'أحادي هادئ'],
                                          ['amber',   'كهرماني', 'ذهبي دافئ'],
                                          ['ocean',   'محيطي',   'سماوي-أزرق'],
                                      ];
                                      foreach ($__paletteRows as $__p):
                                    ?>
                                      <button type="button"
                                              class="appearance-card"
                                              data-palette-id="<?= $__p[0] ?>"
                                              onclick="window.secSelectPalette && window.secSelectPalette('<?= $__p[0] ?>')">
                                          <div class="appearance-card__swatch"></div>
                                          <div class="appearance-card__preview">
                                              <div class="appearance-card__btn"></div>
                                              <div class="appearance-card__bar"></div>
                                              <div class="appearance-card__bar short"></div>
                                          </div>
                                          <div class="appearance-card__label arabic-text"><?= $__p[1] ?></div>
                                          <div class="appearance-card__hint arabic-text"><?= $__p[2] ?></div>
                                      </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Dark / Light -->
                        <div class="setting-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-label mb-0 arabic-text">الوضع الحالي</label>
                                    <div class="form-text arabic-text">التبديل اليدوي يُوقِف التبديل التلقائي حسب الوقت ويُحفظ فوراً</div>
                                </div>
                                <label class="switch" for="secCurrentModeInput">
                                    <input id="secCurrentModeInput" type="checkbox"
                                           onchange="secUpdatePreference('theme', this.checked ? 'dark' : 'light')" />
                                    <div class="slider round">
                                        <div class="sun-moon">
                                            <svg class="moon-dot" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                                            <svg class="light-ray" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                                            <svg class="cloud-dark" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                                            <svg class="cloud-light" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50"></circle></svg>
                                        </div>
                                        <div class="stars">
                                            <svg class="star" viewBox="0 0 20 20"><path d="M 0 10 C 10 10,10 10 ,0 10 C 10 10 , 10 10 , 10 20 C 10 10 , 10 10 , 20 10 C 10 10 , 10 10 , 10 0 C 10 10,10 10 ,0 10 Z"></path></svg>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Auto schedule -->
                        <div class="setting-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-label mb-0 arabic-text">تبديل تلقائي حسب الوقت</label>
                                    <div class="form-text arabic-text">عند التفعيل يختار التطبيق الوضع الداكن أو الفاتح تلقائياً حسب الأوقات أدناه</div>
                                </div>
                                <div class="toggle-switch-wrapper">
                                    <input type="checkbox" class="toggle-switch" id="secThemeAutoSchedule"
                                           onchange="secSaveAutoSchedule()" />
                                </div>
                            </div>
                        </div>

                        <div class="setting-item theme-schedule-times" id="secThemeScheduleTimes" hidden>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label arabic-text" for="secThemeDarkFrom">بداية الوضع الداكن</label>
                                    <div class="form-text mb-2 arabic-text">الساعة التي يبدأ عندها الوضع الداكن</div>
                                    <input type="time" class="form-control" id="secThemeDarkFrom" value="19:00"
                                           onchange="secSaveAutoSchedule()" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label arabic-text" for="secThemeLightFrom">بداية الوضع الفاتح</label>
                                    <div class="form-text mb-2 arabic-text">الساعة التي يعود عندها الوضع الفاتح</div>
                                    <input type="time" class="form-control" id="secThemeLightFrom" value="07:00"
                                           onchange="secSaveAutoSchedule()" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="settings-section">
                        <h5 class="arabic-text"><i class="bi bi-ui-checks me-2"></i>التفضيلات</h5>

                        <div class="setting-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-label mb-0 arabic-text">زر العودة للأعلى</label>
                                    <div class="form-text arabic-text">إظهار أو إخفاء زر التمرير لأعلى الصفحة</div>
                                </div>
                                <div class="toggle-switch-wrapper">
                                    <input type="checkbox" class="toggle-switch" id="secBackToTopDisplay"
                                           onchange="secUpdatePreference('back_to_top_display', this.checked)" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="/app/Views/secretary/assets/js/settings.js?v=<?= file_exists(__DIR__ . '/assets/js/settings.js') ? filemtime(__DIR__ . '/assets/js/settings.js') : time() ?>"></script>
