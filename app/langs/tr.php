<?php
return [
    "errors"=>[
        "notAvailable"=>"Seçtiğiniz tarihlerde villamız uygun değildir.Lütfen başka bir tarih aralığı seçiniz.",
        "minNight"=>"Seçtiğiniz tarihler minimum konaklama süresinin altındadır.",
        "notPrice"=>"Fiyat olan tarihlerden seçim yapınız.",
        "hasOption"=>"Bu tarihlerde ödeme bekleyen başka bir rezervasyon bulunmaktadır.",
        "notActive"=>"Bu villa rezervasyon alınımına kapalıdır. Dilerseniz diğer villalarımızı inceleyebilirsiniz.",
        "invalidParameter"=>"Eksik parametre",
        "invalidDates"=>"Tarih seçiniz.",
        "reservertionNotSended"=>"Rezervasyon gönderme işlemi sırasında bir sorun oluştu.",
        "phoneError"=>"Geçersiz Telefon Numarası",
        "contractError"=>"Lütfen sözleşmeleri okuyup onayladığınızı doğrulayın.",
        "sms"=>[
            "20"=>"Mesaj metninde ki problemden dolayı gönderilemediğini veya standart maksimum mesaj karakter sayısını geçtiğini ifade eder.",
            "30"=>"Geçersiz kullanıcı adı , şifre veya kullanıcınızın API erişim izninin olmadığını gösterir.Ayrıca eğer API erişiminizde IP sınırlaması yaptıysanız ve sınırladığınız ip dışında gönderim sağlıyorsanız 30 hata kodunu alırsınız. API erişim izninizi veya IP sınırlamanızı , web arayüzden; sağ üst köşede bulunan ayarlar> API işlemleri menüsunden kontrol edebilirsiniz.",
            "40"=>"Mesaj başlığınızın (gönderici adınızın) sistemde tanımlı olmadığını ifade eder. Gönderici adlarınızı API ile sorgulayarak kontrol edebilirsiniz.",
            "50"=>"Abone hesabınız ile İYS kontrollü gönderimler yapılamamaktadır.",
            "51"=>"Aboneliğinize tanımlı İYS Marka bilgisi bulunamadığını ifade eder.",
            "70"=>"Hatalı sorgulama. Gönderdiğiniz parametrelerden birisi hatalı veya zorunlu alanlardan birinin eksik olduğunu ifade eder.",
            "80"=>"Gönderim sınır aşımı.",
            "85"=>"Mükerrer Gönderim sınır aşımı. Aynı numaraya 1 dakika içerisinde 20'den fazla görev oluşturulamaz.",
            "00"=>"Gönderdiğiniz SMS'inizin başarıyla sistemimize ulaştığını gösterir. 00 : Mesajınızın tarih formatına ilişkin bir hata olmadığı anlamına gelir. 123xxxxxx : Gönderilen SMSe ait ID bilgisi, Bu görevid (bulkid) niz ile mesajınızın iletim raporunu sorguyabilirsiniz.",
            "01"=>"Gönderdiğiniz SMS'inizin başarıyla sistemimize ulaştığını gösterir. 01 : Mesajınızın başlangıç tarihine ilişkin bir hata olduğunu gösterir, sistem tarihi ile değiştirilip işleme alınmıştır. 123xxxxxx : Gönderilen SMSe ait ID bilgisi, Bu görevid (bulkid) niz ile mesajınızın iletim raporunu sorguyabilirsiniz.",
            "02"=>"	Gönderdiğiniz SMS'inizin başarıyla sistemimize ulaştığını gösterir. 02 : Mesajınızın sonlandırma tarihine ilişkin bir hata olduğunu gösterir, sistem tarihi ile değiştirilip işleme alınmıştır. 123xxxxxx : Gönderilen SMSe ait ID bilgisi, Bu görevid (bulkid) niz ile mesajınızın iletim raporunu sorguyabilirsiniz.",
        ],
        "invalidReservationNumber"=>"Geçersiz rezervasyon numarası",
        "searchMin"=>"Aramak için minumum 3 karakter giriniz.",
        "payment"=>[
            "reservationNotAvailableForIyzico"=>"Bu rezervasyon iyzico ile ödemeye kapalıdır.",
            "reservationNotFound"=>"Reservasyon bulunamadı.",
            "notAuth"=>"Lütfen giriş yapınız.",
            "invalidToken"=>"Geçersiz token bilgisi"
        ],
        "reservationNotFound"=>"Girdiğiniz bilgilere göre rezervasyon bulunamamıştır.",
        "invalidUser"=>"Geçersiz kullanıcı bilgisi.",
        "bankTransfer"=>[
            "success"=>"İşlem başarılı",
            "notAuth"=>"Lütfen giriş yapınız.",
            "invalidToken"=>"Geçersiz token bilgisi"
        ],
        "invoice"=>[
            "error"=>"Lütfen kişi ve fatura bilgilerini giriniz."
        ],
        "notAuth"=>"Lütfen giriş yapınız.",
        "invalidToken"=>"Geçersiz token bilgisi",
        "paymentSuccess"=>[
            "invalidParam"=>"Eksik veya hatalı parametre.",
            "paymentNotFound"=>"Bu siparişe ait herhangi bir ödeme bulunamadı.",
            "reservationNotFound"=>"Reservasyon Bulunamadı.",
            "reservationPaymentTypeError"=>"Rezervasyon durumu değiştirilirken bir sorun oluştu.",
            "mainNotSended"=>"Mail Gönderilirken bir sorun oluştu.(Ödeme işlemleri ve rezervasyon gerçekleşti.Girdiğiniz mail adresi hatalı olabilir.Rezervasyon kontrolünü firmamızı arayarak doğrulayınız.)"
        ]
    ],
    "priceList"=>[
        "subTitle"=>"Minumum {night} gece konaklama",
        "cleaningFeeInfo"=>"{night} Gece altındaki kiralamalarda ekstra {price} temizlik ücreti alınmaktadır.",
        "notFound"=>"Herhangi bir fiyat bilgisi bulunamadı."
    ],
    "shortTerms"=>"{month} Ayı {night} Günlük Kiralık Villalar",
    "shortTermsDescription"=>"{month} Ayı {night} günlük villa kiralayın! En uygun fiyat teklifi ile, istediğiniz zamana uygun villa seçeneklerine göz atın. Villa Villam",
    "success"=>"İşlem başarılı",
    "reservationMailTitle"=>"Talebiniz incelemeye alındı. ({villaName})",
    "reservationMailTitleSite"=>"Yeni Rezervasyon Talebi ({villaName})",
    "mail"=>[
        "deposit"=>"Ön Ödeme Tutarı",
        "payOnArrival"=>"Villaya Girişte Ödenecek Tutar",
        "cleaningFee"=>"Temizlik Ücreti",
        "electricityFee"=>"Elektrik Ücreti",
    ],
    "paymentSuccessMail"=>[
        "title"=>"Ödeme yapıldı. ({ReservationId})",
    ],
    "offerMail"=>[
        "name"=>"İsim",
        "email"=>"E-posta",
        "phone"=>"Telefon",
        "see"=>"Gör",
        "seeOffers"=>"Teklifleri Gör",
        "title"=>"Teklif İsteği Gönderildi"
    ],
    "promotionCode"=>[
        "notFound"=>"Bu kod mevcut degil.",
        "invalidCode"=>"Eksik veya hatalı parametre."
    ],
    "showAll"=>"Tümünü Göster",
    "reservation" => [
        "onlyFirstPaymentValidationError" => "Bu villa sadece Ön Ödeme Kabul ediyor.",
        "onlyFullPaymentValidationError"=>"Bu villa sadece Tamamını Öde seçeneğini kabul ediyor.",
        "reservationError"=>"Rezervasyon gönderme işlemi sırasında bir sorun oluştu.",
        "phoneValidationError"=>"Telefon alanlarını doldurduğunuzdan emin olun.",
        "agreementValidationError"=>"Lütfen sözleşmeleri okuyup onayladığınızı doğrulayın.",
        "success"=>"Talep Başarılı."
    ],
    "sendReservationMail"=>[
        "title"=>"Talebiniz incelemeye alındı. ({villaName})",
        "titleSite"=>"Yeni Rezervasyon Talebi ({villaName})"
    ],
    "paymentError"=>"Ödeme Başarısız",
    "MailTemplate"=>[
        "OnOdeme"=>"Ön Ödeme Tutarı"
    ],
    "invoiceError"=>"Lütfen kişi ve fatura bilgilerini giriniz.",
    "notAuth"=>"Lütfen giriş yapınız.",
    "invalidToken"=>"Geçersiz token bilgisi"
];