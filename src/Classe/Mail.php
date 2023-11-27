<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    private $api_key = '140fff8ce10403573b3bd5e9ceebd120';
    private $api_key_secret = 'f3796b68412ff927edcef1c322e633fa';

    public function send($to_email, $to_name, $subject, $content, $templateId)
    {   
        $mj = new Client($this->api_key, $this->api_key_secret,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "armurerie@arsenal-pro.com",
                        'Name' => "Arsenal Pro Armurerie"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => $templateId,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ],
                    
                ]
            ]
        ];
        
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
    public function sendSearchResults($to_email)
    {   
        $fichier = "/var/www/arsenal/sauvegarde_recherche.txt";
        $mj = new Client($this->api_key, $this->api_key_secret,true,['version' => 'v3.1']);
        $date = new \DateTimeImmutable('now -1 day');
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "armurerie@arsenal-pro.com",
                        'Name' => "Arsenal Pro Armurerie"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => "ARSENAL PRO"
                        ]
                    ],
                    'TemplateID' => 4639500,
                    'TemplateLanguage' => true,
                    'Subject' => "Termes de recherches mensuel site",
                    'Variables' => [
                        'content' => "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
                        <div>
                        <strong>Recapitulatif mensuel des termes recherchés sur arsenal-pro.fr pour le mois de ". $date->format('m/Y') ."</strong>
                        </div></section></section>"
                    ],
                    'Attachments' => [
                        [
                            'ContentType' => "text/plain",
                            'Filename' => "recherche_mensuel_arsenal_pro.txt",
                            'Base64Content' => base64_encode(file_get_contents($fichier))
                        ]
                    ],
                    
                ]
            ]
        ];
        echo "Envoi mail mensuel sur les termes de recherches...";
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
    public function sendAvecFichierPDF($to_email, $to_name, $subject, $content, $fichier, $nomfichier, $templateId)
    {   
        $mj = new Client($this->api_key, $this->api_key_secret,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "armurerie@arsenal-pro.com",
                        'Name' => "Arsenal Pro Armurerie"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => $templateId,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ],
                    'Attachments' => [
                        [
                            'ContentType' => "application/pdf",
                            'Filename' => $nomfichier,
                            'Base64Content' => base64_encode(file_get_contents($fichier))
                        ]
                    ],
                    
                ]
            ]
        ];
        
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
    public function sendExcel($to_email, $to_name, $subject, $fichier, $nomfichier, $content){

        $mj = new Client($this->api_key, $this->api_key_secret,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "armurerie@arsenal-pro.com",
                        'Name' => "Arsenal Pro Armurerie"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 4639500,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ],
                    'Attachments' => [
                        [
                            'ContentType' => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                            'Filename' => $nomfichier,
                            'Base64Content' => base64_encode(file_get_contents($fichier))
                        ]
                    ],
                    
                ]
            ]
        ];
        
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
    public function sendFacturesRecapMensuel($to_email)
    {   
        $fichier = "/var/www/arsenal/factures/backup/facture-" . date('m-Y', strtotime("-1 months")) . "-recap.tar.gz";
        $mj = new Client($this->api_key, $this->api_key_secret,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "armurerie@arsenal-pro.com",
                        'Name' => "Arsenal Pro Armurerie"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => "ARSENAL PRO"
                        ]
                    ],
                    'TemplateID' => 4639500,
                    'TemplateLanguage' => true,
                    'Subject' => "FACTURES DU MOIS : ". date('m-Y', strtotime("-1 months")),
                    'Variables' => [
                        'content' => "<section style='font-family: arial; color: black;'> <section style='width: 95%; margin: auto; padding: 15px 0;'>
                        <div>
                        <strong>Recapitulatif mensuel factures sur arsenal-pro.fr</strong>
                        </div>
                        </section></section>"
                    ],
                    'Attachments' => [
                        [
                            'ContentType' => "application/x-gzip",
                            'Filename' => "factures-" . date('m-Y', strtotime("-1 months")) ."-recap.tar.gz",
                            'Base64Content' => base64_encode(file_get_contents($fichier)) //récupérer à partir du serveur UNIX (le fichier est généré via un script bash)
                        ]
                    ],
                    
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}
