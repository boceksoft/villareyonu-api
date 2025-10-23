<!--#include file="../../httpdocs/inc.asp"-->
<%
On Error Resume Next

    rezid=rq("rezid")
    if not rezid="" then
        set bocek = server.createobject("adodb.recordset")
        sql=""&_
        "select kayitlar.id as kayitid,"&_
        "DATEDIFF(DAY,rez_tarihi,gelecek_tarih) as gece,"&_
        "destinations.baslik"&uzanti&" as bolge_baslik,"&_
        "d2.baslik"&uzanti&" as bolge_ust_baslik,"&_
        "homes.giris_saat,homes.cikis_saat,homes.enlem,homes.boylam,homes.url,homes.baslik,"&_
        "convert(varchar,kayitlar.rez_tarihi,104) as rez_tarihix,convert(varchar,kayitlar.gelecek_tarih,104) as gelecek_tarihx,"&_
        "kayitlar.*, "&_
        "dbo.FnRandomSplit(homes.resim"&uzanti&",',') as resim, "&_
        "homes.baslik"&uzanti&" as baslik, "&_
        "convert(varchar,convert(date,kayitlar.rez_tarihi,104),103) as t1, "&_
        "convert(varchar,convert(date,kayitlar.gelecek_tarih,104),103) as t2 "&_
        "from homes "&_
        "inner join kayitlar on kayitlar.evid=homes.id "&_
        "inner join destinations on destinations.id=homes.emlak_bolgesi "&_
        "inner join destinations as d2 on d2.id=destinations.cat "&_
        "where kayitlar.id="&rezid&" "

        bocek.open SQL,baglan,1,3
        if not bocek.eof then
            bocek("sozlesme") = true
            bocek("sozlesme_send_date") = now()
            bocek.update

            tarih1 = bocek("rez_tarihi")
            tarih2 = bocek("gelecek_tarih")

            sonuc = dateDiff("d",tarih1,tarih2)

            ekucretler=""
		    if not bocek("ekucretler")="" then
		        ekucretler=replace(bocek("ekucretler"),"&&","----")
		    end if

            'Admin panelinden pdf gönderilince bu sablon gidiyor. Burda kalsın belki değişir diye silmedim
            sablonsite="https://noproxy.villareyonu.com/villareyonu.com/htmlsablon/sablon1.asp?islem=pdfgonder;/isim="&bocek("musteri")&_
                    ";/kisi="&bocek("yetiskin")&" yetişkin "&bocek("cocuk")&" cocuk;/gece="&sonuc&" Gece;/villakodu="&bocek("adi")&_
                    ";/villaya_giriste_odenecek="&bocek("kalan")&";/enlem="&bocek("enlem")&";/boylam="&bocek("boylam")&_
                    ";/villaurl="&bocek("url")&";/rstart_date_="&bocek("rez_tarihix")&";/rend_date_="&bocek("gelecek_tarihx")&_
                    ";/saat1="&bocek("giris_saat")&";/saat2="&bocek("cikis_saat")&";/temizlik="&bocek("temizlik")

            'Buda Rez Tamamlandı şablonu. Üsttekini yok sayıp bu şablon ile pdf i gönderiyor
            sablonsite="https://noproxy.villareyonu.com/yeni-mail-sablon.asp?islem=odemetamamlandi;/id="&bocek("id")&";/musteri="&bocek("musteri")



            pdfpath="https://pdf.boceksoft.com/?url="&qsql("domain")&"/boceksoft-vr-v2/belgeler/sozlesme.asp?id="&rezid&";/site=1"&_
                    "&icerik=&dosyaadi=sozlesme&dosya=Sozlesme&siteadi="&qsql("siteadi")&"&smtp="&qsql("smtp")&"&sitemail="&qsql("sitemail")&_
                    "&mailsifre="&qsql("sitemailsifre")&"&kime="&bocek("email")&"&mailport=587&genislik=1000&sablonsite="&sablonsite&_
                    "&mailbasik="&qsql("siteadi")&" Rezervasyon Sozlesmesi&mailkonu=Odeme Tamamlandi! ("&bocek("id")&") | Kiralama Evraklari&ssl="&qsql("ssl")
                Set oXMLHttp = Server.CreateObject("Msxml2.ServerXMLHTTP.6.0")
                oXMLHttp.open "GET", pdfpath, False
                oXMLHttp.Send()
                Set oXMLHttp = Nothing

                If Err.Number <> 0 Then
                    'Response.Write "Hata: " & Err.Description
                End If
        end if
    end if
%>