<?php

class BreadCrumb
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
        $Home = Page::GetById(1);
        $url = $this->Page['url'];

        //remove if url start with /
        $url = ltrim($url,"/");


        switch ($this->Routing["RoutingTypeId"]){



            case "ProductDetail":
                $Path = ProductCategory::GetById(39);
                $return = [
                    "baslik"=>$this->Page["baslik"],
                    "data"=>[
                        [
                            "url"=> "/",
                            "name"=> $Home["baslik"],
                            "title"=>$Home["title"]
                        ],
                        [
                            "url"=> "/".$Path['url'],
                            "name"=> $Path['baslik'],
                            "title"=>$Path["title"]
                        ],
                        [
                            "url"=> "/".$url,
                            "name"=> $this->Page['baslik'],
                            "title"=>$this->Page['title']
                        ],

                    ]

                ];

                break;
            case "ProductSearch":
            case "ProductCategory" :

                $Path = ProductCategory::GetById(1);
                $return = [
                    "baslik"=> $this->Page['baslik'],
                    "data"=>[
                        [
                            "url"=> "/",
                            "name"=> "Anasayfa",
                            "title"=>$Home["title"]
                        ],
                        [
                            "url"=> $this->Page["url"],
                            "name"=> $this->Page["baslik"],
                            "title"=>$this->Page["title"],
                        ]

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
                        "@id"=> $qsql["domain"]."/".$url,
                        "url"=> $qsql["domain"]."/".$url,
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
                    "baslik"=> $this->Page['baslik'],
                    "data"=>[
                        [
                            "url"=> $qsql["domain"],
                            "name"=> "Anasayfa",
                            "title"=>$Home["title"]
                        ],
                        [
                            "url"=> $qsql["domain"]."/".$url,
                            "name"=> $this->Page['baslik'],
                            "title"=> $this->Page['title'],
                        ]
                    ]
            ];
                break;
            case "BlogDetail":
                $Blog = Page::GetById(10,"/");
                $return = [
                    "baslik"=> $this->Page['baslik'],
                    "data"=>[
                        [
                            "url"=> $qsql["domain"],
                            "name"=> "Anasayfa",
                            "title"=>$Home["title"]
                        ],
                        [
                            "url"=> $Blog["url"],
                            "name"=> $Blog["baslik"],
                            "title"=>$Blog["title"]
                        ],
                        [
                            "url"=> $Blog["url"]."/".$url,
                            "name"=> $this->Page['baslik'],
                            "title"=> $this->Page['title'],
                        ]
                    ]
                ];
                break;
            default:
                $return = [
                    "baslik"=>$this->Page['baslik'],
                    "data"=>[
                        [
                            "url"=> "/",
                            "name"=> $Home["baslik"],
                            "title"=>$Home["title"]
                        ],
                        [
                            "url"=> "/".$url,
                            "name"=> $this->Page['baslik'],
                            "title"=> $this->Page['title'],
                        ]
                    ]
                ];
        }


        return $return;

    }
}