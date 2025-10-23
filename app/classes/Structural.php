<?php

class Structural
{
    public $Export;

    public  $Routing;
    public  $Page;
    public $ProductDetailBase;



    public function __construct($Routing,$Page)
    {
        global $config;
        $this->Routing = $Routing;
        $this->Page = $Page;
        $this->ProductDetailBase=$config["BaseUrl"];
    }

    public function Result (): array
    {
        global $qsql;
        global $config;
        $return = [];

        switch ($this->Routing["RoutingTypeId"]){
            case "Home":
                $return = [
                    [
                        "id" => "Corporation",
                        "content"=>[
                            "@context"=> "http://schema.org",
                            "@type"=> "Corporation",
                            "name"=> $qsql["siteadi"],
                            "alternateName"=> "villa kiralama",
                            "url"=> $qsql["domain"]."/",
                            "logo"=> CDN."/img/logo.png",
                            "address" => [
                                "@type"=>"PostalAddress",
                                "addressLocality"=>"Muğla",
                                "addressCountry" => "Türkiye",
                                "postalCode"=>"48300",
                                "streetAddress"=> $qsql["adres"]
                            ],
                            "contactPoint" => [
                                "@type"=> "ContactPoint",
                                "telephone"=> str_replace(" ","-",$qsql["adres"]),
                                "contactType"=> "reservations",
                                "contactOption"=> "TollFree",
                                "areaServed"=> "TR",
                                "availableLanguage"=> "Turkish"
                            ],
                            "sameAs" => [
                                $qsql["facebook"],
                                $qsql["instagram"]
                            ]
                        ]
                    ],
                    [
                        "id" => "BreadcrumbList",
                        "content"=>[
                            "@context"=> "http://schema.org",
                            "@type"=> "BreadcrumbList",
                            "@id"=> $qsql["domain"]."/#breadcrumb",
                            "itemListElement"=> [
                                "@type"=> "ListItem",
                                "position"=> 1,
                                "item"=> [
                                    "@type"=> "WebPage",
                                    "@id"=> $qsql["domain"],
                                    "name"=> "Anasayfa"
                                ]
                            ]
                        ]
                    ],
                    [
                        "id" => "CollectionPage",
                        "content"=>[
                            "@context"=> "http://schema.org",
                            "@type"=> "CollectionPage",
                            "@id"=> $qsql["domain"]."/#collectionPage",
                            "@url"=>$qsql["domain"]."/",
                            "name"=>$this->Page["title"],
                            "breadcrumb"=>[
                                "@type"=>"BreadcrumbList",
                                "@id"=>$qsql["domain"]."/#breadcrumb"
                            ],
                            "isPartOf"=>[
                                "@id"=>$qsql["domain"]."/#website"
                            ],
                            "description"=>$this->Page["description"],
                            "inLanguage"=>"tr",
                            "potentialAction"=>[
                                [
                                    "@type"=>"ReadAction",
                                    "target"=>[
                                        $qsql["domain"]."/"
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        "id" => "WebSite",
                        "content"=>$this->Website()
                    ],
                    [
                        "id" => "Organization",
                        "content"=>$this->Organization()
                    ]
                ];
                break;
            case "ProductDetail":
                $Path = ProductCategory::GetById(39);
                $return = [
                    [
                        "id" => "BreadcrumbList",
                        "content"=>[
                            "@context"=> "http://schema.org",
                            "@type"=> "BreadcrumbList",
                            "@id"=> $qsql["domain"]."/".$this->Page["url"]."/#breadcrumb",
                            "itemListElement"=> [
                                [
                                    "@type"=> "ListItem",
                                    "position"=> 1,
                                    "item"=> [
                                        "@type"=> "WebPage",
                                        "@id"=> $qsql["domain"],
                                        "url"=> $qsql["domain"],
                                        "name"=> "Anasayfa"
                                    ]
                                ],
                                [
                                    "@type"=> "ListItem",
                                    "position"=> 2,
                                    "item"=> [
                                        "@type"=> "Thing",
                                        "@id"=> $qsql["domain"]."/".$Path['url'],
                                        "url"=> $qsql["domain"]."/".$Path['url'],
                                        "name"=> $Path['title']
                                    ]
                                ],
                                [
                                    "@type"=> "ListItem",
                                    "position"=> 3,
                                    "item"=> [
                                        "@type"=> "Website",
                                        "@id"=> $qsql["domain"]."/".$this->Page['url'],
                                        "url"=> $qsql["domain"]."/".$this->Page['url'],
                                        "name"=> $this->Page['title']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        "id"=>"WebPage",
                        "content"=>$this->WebPage()
                    ],
                    [
                        "id"=>"Product",
                        "content"=>$this->Product()
                    ],
                    [
                        "id" => "WebSite",
                        "content"=>$this->Website()
                    ],
                    [
                        "id" => "Organization",
                        "content"=>$this->Organization()
                    ]
                ];

                break;
            case "ProductSearch":
            case "ProductCategory" :

                $Path = ProductCategory::GetById(1);
                $return = [
                    [
                        "id" => "BreadcrumbList",
                        "content"=>[
                            "@context"=> "http://schema.org",
                            "@type"=> "BreadcrumbList",
                            "@id"=> $qsql["domain"]."/".$this->Page["url"]."/#breadcrumb",
                            "itemListElement"=> [
                                [
                                    "@type"=> "ListItem",
                                    "position"=> 1,
                                    "item"=> [
                                        "@type"=> "WebPage",
                                        "@id"=> $qsql["domain"],
                                        "url"=> $qsql["domain"],
                                        "name"=> "Anasayfa"
                                    ]
                                ],
                                [
                                    "@type"=> "ListItem",
                                    "position"=> 2,
                                    "item"=> [
                                        "@type"=> "Thing",
                                        "@id"=> $qsql["domain"]."/".$Path['url'],
                                        "url"=> $qsql["domain"]."/".$Path['url'],
                                        "name"=> $Path['title']
                                    ]
                                ],
                                [
                                    "@type"=> "ListItem",
                                    "position"=> 3,
                                    "item"=> [
                                        "@type"=> "Website",
                                        "@id"=> $qsql["domain"]."/".$this->Page['url'],
                                        "url"=> $qsql["domain"]."/".$this->Page['url'],
                                        "name"=> $this->Page['title']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        "id" => "CollectionPage",
                        "content"=>$this->CollectionPage()
                    ],
                    [
                        "id" => "WebSite",
                        "content"=>$this->Website()
                    ],
                    [
                        "id" => "Organization",
                        "content"=>$this->Organization()
                    ],
                    [
                        "id"=>"Faq",
                        "content"=>$this->Faq()
                    ]
                ];

                break;
            case "ProductDestination":
                $Path = ProductCategory::GetById(1);
                $NextPosition = 3;
                $BreadcrumbAdd = [];


                $Parent = Destination::GetById($this->Page["cat"]);
                if ($Parent){
                    $BaseParent = Destination::GetById($Parent["cat"]);
                }

                if ($BaseParent){
                    $BreadcrumbAdd[] = [
                        "@type"=> "ListItem",
                        "position"=> $NextPosition,
                        "item"=> [
                            "@type"=> "Thing",
                            "@id"=> $qsql["domain"]."/".$BaseParent['url'],
                            "url"=> $qsql["domain"]."/".$BaseParent['url'],
                            "name"=> $BaseParent['title']." Kiralık Villalar"
                        ]
                    ];
                    $NextPosition++;
                }

                if ($Parent) {
                    $BreadcrumbAdd[] = [
                        "@type"=> "ListItem",
                        "position"=> $NextPosition,
                        "item"=> [
                            "@type"=> "Thing",
                            "@id"=> $qsql["domain"]."/".$Parent['url'],
                            "url"=> $qsql["domain"]."/".$Parent['url'],
                            "name"=> $Parent['title']." Kiralık Villalar"
                        ]
                    ];
                    $NextPosition++;
                }

                $BreadcrumbAdd[] = [
                    "@type"=> "ListItem",
                    "position"=> $NextPosition,
                    "item"=> [
                        "@type"=> "Thing",
                        "@id"=> $qsql["domain"]."/".$this->Page['url'],
                        "url"=> $qsql["domain"]."/".$this->Page['url'],
                        "name"=> $this->Page['title']." Kiralık Villalar"
                    ]
                ];


                    $return = [
                    [
                        "id" => "BreadcrumbList",
                        "content"=>[
                            "@context"=> "http://schema.org",
                            "@type"=> "BreadcrumbList",
                            "@id"=> $qsql["domain"]."/".$this->Page["url"]."/#breadcrumb",
                            "itemListElement"=> [
                                [
                                    "@type"=> "ListItem",
                                    "position"=> 1,
                                    "item"=> [
                                        "@type"=> "WebPage",
                                        "@id"=> $qsql["domain"],
                                        "url"=> $qsql["domain"],
                                        "name"=> "Anasayfa"
                                    ]
                                ],
                                [
                                    "@type"=> "ListItem",
                                    "position"=> 2,
                                    "item"=> [
                                        "@type"=> "Thing",
                                        "@id"=> $qsql["domain"]."/".$Path['url'],
                                        "url"=> $qsql["domain"]."/".$Path['url'],
                                        "name"=> $Path['title']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        "id" => "CollectionPage",
                        "content"=>$this->CollectionPage()
                    ],
                    [
                        "id" => "WebSite",
                        "content"=>$this->Website()
                    ],
                    [
                        "id" => "Organization",
                        "content"=>$this->Organization()
                    ]
                ];
                $return[0]["content"]["itemListElement"]=array_merge($return[0]["content"]["itemListElement"],$BreadcrumbAdd) ;





                break;
            case "Contact":
                $return = [
                    [
                        "id" => "BreadcrumbList",
                        "content"=>[
                            "@context"=> "http://schema.org",
                            "@type"=> "BreadcrumbList",
                            "@id"=> $qsql["domain"]."/".$this->Page["url"]."/#breadcrumb",
                            "itemListElement"=> [
                                [
                                    "@type"=> "ListItem",
                                    "position"=> 1,
                                    "item"=> [
                                        "@type"=> "WebPage",
                                        "@id"=> $qsql["domain"],
                                        "url"=> $qsql["domain"],
                                        "name"=> "Anasayfa"
                                    ]
                                ],
                                [
                                    "@type"=> "ListItem",
                                    "position"=> 2,
                                    "item"=> [
                                        "@type"=> "Website",
                                        "@id"=> $qsql["domain"]."/".$this->Page['url'],
                                        "url"=> $qsql["domain"]."/".$this->Page['url'],
                                        "name"=> $this->Page['title']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        "id" => "WebPage",
                        "content"=>$this->WebPage()
                    ],
                    [
                        "id"=>"LocalBusiness",
                        "content"=>[
                            "@context"=>"http://schema.org",
                            "@type"=>"LocalBusiness",
                            "image"=>CDN."/img/logo.png",
                            "name"=>$qsql["siteadi"],
                            "description"=>$this->Page["description"],
                            "telephone"=>$qsql["telefon"],
                            "address"=>[
                                "@type"=>"PostalAddress",
                                "streetAddress"=>str_replace("<br>","",$qsql["adres"]),
                                "addressLocality"=>"Antalya",
                                "addressRegion"=>"Kaş",
                                "telephone"=>$qsql["telefon"]
                            ]
                        ]
                    ],
                    [
                        "id" => "WebSite",
                        "content"=>$this->Website()
                    ],
                    [
                        "id" => "Organization",
                        "content"=>$this->Organization()
                    ]
                ];
                break;
            default:
                $return = [
                    [
                        "id" => "BreadcrumbList",
                        "content"=>[
                            "@context"=> "http://schema.org",
                            "@type"=> "BreadcrumbList",
                            "@id"=> $qsql["domain"]."/".$this->Page["url"]."/#breadcrumb",
                            "itemListElement"=> [
                                [
                                    "@type"=> "ListItem",
                                    "position"=> 1,
                                    "item"=> [
                                        "@type"=> "WebPage",
                                        "@id"=> $qsql["domain"],
                                        "url"=> $qsql["domain"],
                                        "name"=> "Anasayfa"
                                    ]
                                ],
                                [
                                    "@type"=> "ListItem",
                                    "position"=> 2,
                                    "item"=> [
                                        "@type"=> "Website",
                                        "@id"=> $qsql["domain"]."/".$this->Page['url'],
                                        "url"=> $qsql["domain"]."/".$this->Page['url'],
                                        "name"=> $this->Page['title']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        "id" => "CollectionPage",
                        "content"=>$this->CollectionPage()
                    ],
                    [
                        "id" => "WebSite",
                        "content"=>$this->Website()
                    ],
                    [
                        "id" => "Organization",
                        "content"=>$this->Organization()
                    ],
                    [
                        "id"=>"Faq",
                        "content"=>$this->Faq()
                    ]
                ];
        }


        return $return;

    }

    public function Website(): array
    {
        global $qsql;
        return [
            "@context"=> "http://schema.org",
            "@type"=> "WebSite",
            "@id"=> $qsql["domain"]."/#website",
            "url"=>$qsql["domain"],
            "potentialAction"=>[
                [
                    "@type"=>"SearchAction",
                    "target"=>$qsql["domain"]."/arama-sonuclari?searchlocationcode={search_term_string}",
                    "query-input"=> "required name=search_term_string"
                ]
            ]
        ];
    }

    public function Product(): array
    {
        global $qsql;
        $Reviews = Reviews::GetReviews("emlak",$this->Page["id"]);

        $ReviewsTotal = count($Reviews);
        $ReviewsAvg=0;
        if($ReviewsTotal)
            $ReviewsAvg = number_format(array_sum(array_column($Reviews,"puan")) / $ReviewsTotal,2);

        $Product = [
            "@context"=> "http://schema.org",
            "@type"=>"Product",
            "description"=>$this->Page["description"],
            "name"=>$this->Page["baslik"],
            "image"=>CDN."/uploads/".$this->Page["resim"],
            "offers"=> [
                "@type"=> "AggregateOffer",
                "availability"=> "https://schema.org/InStock",
                "url"=> $qsql["domain"]."/".$this->Page["url"],
                "image"=> CDN."/uploads/".$this->Page["resim"],
                "lowPrice"=> $this->Page["minfiyat"],
                "highPrice"=> $this->Page["maxfiyat"],
                "offerCount"=>"1",
                "priceCurrency"=> "TRY"
            ]
        ];


        if ($Reviews){
            $aggregateRating = [
                "@type"=>"AggregateRating",
                "bestRating"=>"5",
                "worstRating"=>"0",
                "ratingValue"=>$ReviewsAvg,
                "reviewCount"=>$ReviewsTotal
            ];
            $Product["aggregateRating"]=$aggregateRating;

            $Review_ =[];
            foreach ($Reviews as $review){
                $date_explode = explode(".",$review["tarih"]);
                $Review_[]=[
                    "@type"=> "Review",
                    "author"=>[
                        "@type"=>"Thing",
                        "name"=> $review["isim"],
                    ],
                    "datePublished"=>$date_explode[2]."-".$date_explode[1]."-".$date_explode[0],
                    "reviewBody"=>$review["mesaj"],
                    "name"=>$this->Page["baslik"],
                    "reviewRating"=>[
                        "@type"=> "Rating",
                        "bestRating"=> "5",
                        "ratingValue"=>$review["puan"],
                        "worstRating"=>"1"
                    ]
                ];
            }
            $Product["review"] = $Review_;
        }

        return $Product;

    }

    public function Organization() :array{
        global $qsql;
        return [
            "@context"=> "http://schema.org",
            "@type"=> "Organization",
            "name"=> $qsql["siteadi"],
            "url"=> $qsql["domain"],
            "logo"=> CDN."/img/logo.png",
        ];
    }

    public function CollectionPage() :array{
        global $qsql;
        return [
            "@context"=> "http://schema.org",
            "@type"=> "CollectionPage",
            "@id"=> $qsql["domain"]."/".$this->Page["url"]."/#collectionPage",
            "@url"=>$qsql["domain"]."/".$this->Page["url"]."/",
            "name"=>$this->Page["title"],
            "breadcrumb"=>[
                "@type"=>"BreadcrumbList",
                "@id"=>$qsql["domain"]."/".$this->Page["url"]."/#breadcrumb"
            ],
            "isPartOf"=>[
                "@id"=>$qsql["domain"]."/#website"
            ],
            "description"=>$this->Page["description"],
            "inLanguage"=>"tr",
            "potentialAction"=>[
                [
                    "@type"=>"ReadAction",
                    "target"=>[
                        $qsql["domain"]."/".$this->Page["url"]."/"
                    ]
                ]
            ]
        ];
    }

    public function WebPage() :array{
        global $qsql;
        global $config;
        $date = new DateTime();
        $date->setTimezone(new DateTimeZone('GMT'));

        $datePublished = $date->format('Y-m-d')."T00:00:00+03:00";
        return [
            "@context"=> "http://schema.org",
            "@type"=> "WebPage",
            "@id"=> $qsql["domain"]."/".$this->Page["url"]."/#website",
            "url"=>$qsql["domain"]."/".$this->Page["url"],
            "name"=>$this->Page["title"],
            "isPartOf"=>[
                "@id"=>$qsql["domain"]."/#website"
            ],
            "primaryImageOfPage"=>[
                "@type"=>"ImageObject",
                "inLanguage"=>"tr",
                "url"=>CDN."/uploads/".$this->Page["resim"]
            ],
            "breadcrumb" => [
                "type"=>"BreadcrumbList",
                "@id"=>$qsql["domain"]."/".$this->Page["url"]."/#breadcrumb"
            ],
            "datePublished"=>$datePublished,
            "dateModified"=>$datePublished,
            "description"=>$this->Page["description"],
            "inLanguage"=>$config["lang"],
            "potentialAction"=>[
                [
                    "@type"=>"ReadAction",
                    "target"=>[
                        $qsql["domain"]."/".$this->Page["url"]."/"
                    ]
                ]
            ]

        ];
    }

    public function Faq(){
        global $qsql;
        if ($this->Routing["RoutingTypeId"]=="ProductCategory"){
            $Faq = ProductCategory::GetFaq($this->Page["id"]);
        }else{
            $Faq = Faq::GetByPageId($this->Page["id"]);
        }
        if ($Faq){
             $r=[
                "@context"=> "http://schema.org",
                "@type"=>"FAQPage",
                "mainEntity"=> []
            ];
             foreach ($Faq as $item){
                 $r["mainEntity"][]=[
                     "@type"=>"Question",
                     "name"=>$item["baslik"],
                     "acceptedAnswer"=>[
                         "@type"=>"Answer",
                         "text"=>strip_tags($item["icerik"])
                     ]
                 ];
             }
        }


        return $r;
    }
}