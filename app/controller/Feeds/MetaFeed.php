<?php

$query = new Query();
//$query->setTop(10);
$query->setQuery("Product");
//$query->addParam("and  h.breadCrumb_kategori=".$explode[1]);
$villas = $query->run();

// XML belgesini oluştur
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>');

// Kanal bilgilerini ekle
$channel = $xml->addChild('channel');
$channel->addChild('title', 'Kiralık Villalar');
$channel->addChild('link', DOMAIN.'/api/MetaFeed.xml');
//  $channel->addChild('description', 'Kiralık villa, tatil villaları, lüks villalar için Türkiye\'nin en güvenilir adresi Villacim\'ı ziyaret edin. Villa kiralamanın en kolay yolu!');

// Villaları ekle
foreach ($villas as $villa) {
    $item = $channel->addChild('item');
    $item->addChild('g:id', $villa['id'], 'http://base.google.com/ns/1.0');
    $item->addChild('g:title', $villa['name'], 'http://base.google.com/ns/1.0');
    $item->addChild('g:description', $villa["description"], 'http://base.google.com/ns/1.0');
    $item->addChild('g:link', DOMAIN.DILURL.$villa['url'], 'http://base.google.com/ns/1.0');
    $item->addChild('g:image_link', CDN."/uploads/".$villa['image'], 'http://base.google.com/ns/1.0');
    $item->addChild('g:availability', "in stock", 'http://base.google.com/ns/1.0');
    $item->addChild('g:price', $villa['minfiyat'], 'http://base.google.com/ns/1.0');
    $item->addChild('g:brand', $qsql["siteadi"], 'http://base.google.com/ns/1.0');
    $item->addChild('g:condition', "new", 'http://base.google.com/ns/1.0');
    $item->addChild('g:google_product_category', "Travel > Vacation Packages", 'http://base.google.com/ns/1.0');
    $item->addChild('g:product_type', "Villa Rentals", 'http://base.google.com/ns/1.0');
}

// XML çıktısını oluştur ve görüntüle
Header('Content-type: text/xml');
echo $xml->asXML();
