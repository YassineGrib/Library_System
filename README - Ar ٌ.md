# نظام المكتبة الرقمية (Digital Library System)

نظام متكامل لإدارة المكتبات الرقمية يتيح للمستخدمين تصفح وتحميل الكتب الإلكترونية، مع نظام إدارة متكامل للمسؤولين.

## المميزات الرئيسية

- **واجهة مستخدم متعددة اللغات**: دعم للغة العربية والإنجليزية والفرنسية
- **نظام تسجيل وتسجيل دخول**: مع نظام موافقة المسؤول على المستخدمين الجدد
- **إدارة الكتب**: إضافة وعرض وتحميل وحذف الكتب
- **نظام اشتراكات**: مع تاريخ انتهاء الاشتراك وحدود التحميل
- **لوحة تحكم للمسؤول**: لإدارة المستخدمين والكتب والفئات
- **واجهة متجاوبة**: تعمل على جميع الأجهزة (الحاسوب، الهاتف، اللوحي)
- **تصميم عصري**: باستخدام Bootstrap 5 وFont Awesome
- **أمان متقدم**: حماية ضد هجمات SQL Injection وXSS

## المتطلبات التقنية

- PHP 7.4 أو أحدث
- MySQL 5.7 أو أحدث
- خادم ويب (Apache/Nginx)
- XAMPP (للتطوير المحلي)

## التثبيت

1. قم بتنزيل أو استنساخ المشروع إلى مجلد `htdocs` في XAMPP:
   ```
   git clone https://github.com/yourusername/library-system.git
   ```

2. قم بإنشاء قاعدة بيانات جديدة باسم `library_system`

3. قم بتعديل ملف الإعدادات في `config/xampp.php` لتحديد معلومات الاتصال بقاعدة البيانات:
   ```php
   return [
       'db' => [
           'host' => 'localhost',
           'name' => 'library_system',
           'user' => 'root',
           'pass' => ''
       ],
       'app' => [
           'base_url' => '/Library_System/'
       ]
   ];
   ```

4. قم بزيارة `http://localhost/Library_System/setup.php` لإعداد قاعدة البيانات وإنشاء حساب المسؤول

5. قم بزيارة `http://localhost/Library_System/update_db.php` لتحديث هيكل قاعدة البيانات (إذا كنت تقوم بالترقية من إصدار سابق)

6. قم بتسجيل الدخول باستخدام حساب المسؤول:
   - البريد الإلكتروني: `admin@library.local`
   - كلمة المرور: `Admin123`

## هيكل المشروع

```
Library_System/
├── admin/                  # صفحات لوحة تحكم المسؤول
├── config/                 # ملفات الإعدادات
├── core/                   # الفئات الأساسية (Database, Auth, etc.)
├── lang/                   # ملفات الترجمة
├── public/                 # الملفات العامة
│   ├── assets/             # CSS, JS, الصور
│   └── uploads/            # الملفات المرفوعة (الكتب، الإيصالات)
├── views/                  # قوالب العرض
│   └── layout/             # قوالب التخطيط المشتركة
├── .htaccess               # إعدادات إعادة التوجيه
├── index.php               # نقطة الدخول الرئيسية
├── setup.php               # سكريبت الإعداد
└── update_db.php           # سكريبت تحديث قاعدة البيانات
```

## دليل الاستخدام

### للمستخدمين

1. **التسجيل**: قم بإنشاء حساب جديد وانتظر موافقة المسؤول
2. **تصفح الكتب**: استعرض الكتب المتاحة حسب الفئة أو استخدم البحث
3. **تحميل الكتب**: يمكنك تحميل الكتب بعد تسجيل الدخول والموافقة على حسابك
4. **الملف الشخصي**: عرض معلومات حسابك وتاريخ انتهاء الاشتراك والكتب التي قمت بتحميلها

### للمسؤولين

1. **إدارة المستخدمين**: الموافقة على المستخدمين الجدد أو رفضهم
2. **إدارة الكتب**: إضافة وتعديل وحذف الكتب
3. **إدارة الفئات**: إنشاء وتعديل فئات الكتب
4. **لوحة المعلومات**: عرض إحصائيات النظام

## الأمان

- تم تنفيذ حماية ضد هجمات SQL Injection باستخدام Prepared Statements
- تم تنفيذ حماية ضد هجمات XSS باستخدام `htmlspecialchars()`
- تم تشفير كلمات المرور باستخدام خوارزمية التجزئة الآمنة
- تم تنفيذ التحقق من الصلاحيات لجميع العمليات

## التخصيص

### تغيير اللغة الافتراضية

قم بتعديل ملف `core/Localization.php`:

```php
private $defaultLanguage = 'ar'; // ar, en, fr
```

### إضافة لغة جديدة

1. قم بإنشاء ملف جديد في مجلد `lang/` (مثل `de.php` للألمانية)
2. انسخ محتوى ملف `en.php` وقم بترجمة القيم
3. قم بتحديث الدالة `getSupportedLanguages()` في ملف `core/Localization.php`

### تغيير مظهر التطبيق

قم بتعديل ملف `public/assets/css/app.css` لتخصيص المظهر العام للتطبيق.

## الترخيص

هذا المشروع مرخص تحت رخصة MIT.

---

تم تطوير هذا المشروع © 2023
