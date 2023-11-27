<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CookieController extends AbstractController
{
    public function getRealIpAddr(){ //https://stackoverflow.com/questions/12553160/getting-visitors-country-from-their-ip
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //trouver IP utilisateur
        {
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //vérifier si ip peut passer par un proxy
        {
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    #[Route('/cookie/creer', name: 'app_creer_cookie')]
    public function creerCookie(Request $req): Response
    {
        $navigateur = "";
        $cookie_nom = $req->get("name");
        $trouverClientAdresse = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=" . $this->getRealIpAddr()); //max requete par minute, 120
        $lati = $trouverClientAdresse->geoplugin_latitude;
        $longi = $trouverClientAdresse->geoplugin_longitude;
        $pays = $trouverClientAdresse->geoplugin_countryName;
        $ville = $trouverClientAdresse->geoplugin_city;
        $adresseClient = "Latitude : " . $lati . " Longitude : " . $longi . " Pays : " . $pays. " Ville : " . $ville . ""; 
        $txt = $cookie_nom . ";" . $lati . ";" . $longi . ";" . $pays. ";" . $ville . ";" . date("Y-m-d") . ";";
        dd($txt);
        
        setcookie($cookie_nom, $adresseClient, time() + (86400 * 30), "/");
        if(isset($_COOKIE[$cookie_nom])) {
            dd($_COOKIE[$cookie_nom]);
        }
        return new Response();
    }
}
