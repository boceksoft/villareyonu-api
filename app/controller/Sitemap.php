<?php



$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset version="2.0" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

$query = $db->prepare("select * from sites");
$query->execute();
foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $site) {


    //Sayfalar
    $query = $db->prepare("select case when id = 1 then '".$site["domain"]."' else '".$site["domain"]."/' + url end as url, dateModified as modified from sayfalar".$site["DBTable"]." where aktif = 1  and sitemap=1  order by siralama asc");
    $query->execute();
    $items = $query->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $category) {
        $url = $xml -> addChild('url');
        $url -> addChild('loc', $category['url']);
        $url -> addChild('changefreq', 'daily');
        $url -> addChild('priority', 1);
        $url -> addChild('lastmod', $category['modified']);
    }

    //Bölgeler
    $query = $db->prepare("select '".$site["domain"]."/' + url".$site["DBTable"]." as url, dateModified as modified from destinations where aktif = 1  order by siralama asc");
    $query->execute();
    $items = $query->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $category) {
        $url = $xml -> addChild('url');
        $url -> addChild('loc', $category['url']);
        $url -> addChild('changefreq', 'daily');
        $url -> addChild('priority', 1);
        $url -> addChild('lastmod', $category['modified']);
    }

    //Tipler
    $query = $db->prepare("select '".$site["domain"]."/' + url".$site["DBTable"]." as url, dateModified as modified from tip where aktif = 1  order by siralama asc");
    $query->execute();
    $items = $query->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $category) {
        $url = $xml -> addChild('url');
        $url -> addChild('loc', $category['url']);
        $url -> addChild('changefreq', 'daily');
        $url -> addChild('priority', 1);
        $url -> addChild('lastmod', $category['modified']);
    }


    //Emlaklar
    $query = $db->prepare("select '".$site["domain"]."/' + url".$site["DBTable"]." as url, dateModified as modified from homes where aktif = 1  order by siralama asc");
    $query->execute();
    $items = $query->fetchAll(PDO::FETCH_ASSOC);
    foreach ($items as $category) {
        $url = $xml -> addChild('url');
        $url -> addChild('loc', $category['url']);
        $url -> addChild('changefreq', 'daily');
        $url -> addChild('priority', 1);
        $url -> addChild('lastmod', $category['modified']);
    }





}

Header('Content-type: text/xml');
echo $xml->asXML();

