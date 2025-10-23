<?php
return [
    "errors"=>[
        "notAvailable"=>"Our villa is not available on the dates you selected. Please select another date range.",
        "minNight"=>"The dates you select are below the minimum stay.",
        "notPrice"=>"Choose from the dates with prices.",
        "hasOption"=>"There is another reservation pending payment for these dates.",
        "notActive"=>"This villa is not available for reservations. If you wish, you can check out our other villas.",
        "invalidParameter"=>"Missing parameter",
        "invalidDates"=>"Select date.",
        "reservertionNotSended"=>"There was a problem submitting the reservation.",
        "phoneError"=>"Invalid Phone Number",
        "contractError"=>"Please confirm that you have read and accepted the agreements.",
        "sms" => [
            "20" => "Indicates that the message could not be sent due to a problem in the message text or exceeding the standard maximum message character count.",
            "30" => "Indicates an invalid username, password, or lack of API access permission for your account. Also, if you have restricted API access by IP and are sending outside of the restricted IP, you will receive error code 30. You can check your API access permission or IP restriction from the web interface; settings> API operations menu located in the top right corner.",
            "40" => "Indicates that your message header (sender name) is not defined in the system. You can check your sender names by querying with the API.",
            "50" => "Sending with the subscriber account is not possible for IYS-controlled dispatches.",
            "51" => "Indicates that there is no IYS Brand information defined for your subscription.",
            "70" => "Invalid query. Indicates that one of the parameters you sent is incorrect or a mandatory field is missing.",
            "80" => "Sending limit exceeded.",
            "85" => "Duplicate sending limit exceeded. More than 20 tasks cannot be created to the same number within 1 minute.",
            "00" => "Indicates that your SMS has successfully reached our system. 00: Indicates that there is no error in the date format of your message. 123xxxxxx: ID information of the sent SMS, you can query the delivery report of your message with this task (bulkid).",
            "01" => "Indicates that your SMS has successfully reached our system. 01: Indicates that there is an error in the start date of your message, it has been replaced with the system date and processed. 123xxxxxx: ID information of the sent SMS, you can query the delivery report of your message with this task (bulkid).",
            "02" => "Indicates that your SMS has successfully reached our system. 02: Indicates that there is an error in the end date of your message, it has been replaced with the system date and processed. 123xxxxxx: ID information of the sent SMS, you can query the delivery report of your message with this task (bulkid)."
        ],
        "invalidReservationNumber"=>"Invalid Reservation Number",
        "searchMin"=>"Please enter at least three characters",
        "payment"=>[
            "reservationNotAvailableForIyzico"=>"This reservation is not available for payment via Iyzico.",
            "reservationNotFound"=>"Reservation not found.",
            "notAuth"=>"Please login.",
            "invalidToken"=>"Invalid token"
        ],
        "reservationNotFound"=>"No reservation was found based on the information you entered.",
        "invalidUser"=>"Invalid User",
        "bankTransfer"=>[
            "success"=>"Transfer is successful.",
            "notAuth"=>"Please login.",
            "invalidToken"=>"Invalid token"
        ],
        "invoice"=>[
            "error"=>"Please enter personal and billing information."
        ],
        "notAuth"=>"Please log in.",
        "invalidToken"=>"Invalid token information.",
        "paymentSuccess"=>[
            "invalidParam"=>"Missing or incorrect parameter.",
            "paymentNotFound"=>"No payment found for this order.",
            "reservationNotFound"=>"Reservation not found.",
            "reservationPaymentTypeError"=>"There was an issue while changing the reservation status.",
            "mainNotSended"=>"There was a problem sending the email. (Payment and reservation processes have been completed. The email address you entered may be incorrect. Please verify the reservation by calling our company.)"
        ]
    ],
    "priceList"=>[
        "subTitle"=>"Minimum {night} night stay",
        "cleaningFeeInfo"=>"For rentals under {night} nights, an extra {price} cleaning fee is charged.",
        "notFound"=>"No price information found."
    ],
    "shortTerms"=>"Villas for Rent for {night} Days in {month}",
    "shortTermsDescription"=>"{night}-day villa rental in {month}! Take a look at the villa options suitable for the time you want with the best price offer.. Villa Villam",
    "success"=>"Transaction successful",
    "reservationMailTitle"=>"Your request has been taken into consideration. ({villaName})",
    "reservationMailTitleSite"=>"New Reservation Request ({villaName})",
    "mail"=>[
        "deposit"=>"Deposit Amount",
        "payOnArrival"=>"Amount to be Paid at Entry to the Village",
        "cleaningFee"=>"Cleaning Fee",
        "electricityFee"=>"Electricity Fee",
    ],
    "paymentSuccessMail"=>[
        "title"=>"Payment Successfull ({ReservationId})",
    ],
    "offerMail"=>[
        "name"=>"Name",
        "email"=>"Email",
        "phone"=>"Phone",
        "see"=>"See",
        "seeOffers"=>"See Offers",
        "title"=>"Offer Request Sent"
    ],
    "promotionCode"=>[
        "notFound"=>"This code does not exist.",
        "invalidCode"=>"Missing or incorrect parameter."
    ],
    "showAll"=>"Show All",
    "reservation" => [
        "onlyFirstPaymentValidationError" => "This villa only accepts a Down Payment.",
        "onlyFullPaymentValidationError" => "This villa only accepts the Full Payment option.",
        "reservationError" => "An error occurred while submitting the reservation.",
        "phoneValidationError" => "Please make sure you have filled in the phone fields.",
        "agreementValidationError" => "Please confirm that you have read and accepted the agreements.",
        "success" => "Request Successful."
    ],
    "sendReservationMail"=>[
        "title"=>"We've received your request for {villaName} and it's now under review.",
        "titleSite"=>"New Reservation Request ({villaName})"
    ],
    "paymentError"=>"Payment Failed",
    "MailTemplate"=>[
        "OnOdeme"=>"Pre Payment"
    ],
    "invoiceError"=>"Please enter personal and billing information.",
    "notAuth"=>"Please log in.",
    "invalidToken"=>"Invalid token information."
];