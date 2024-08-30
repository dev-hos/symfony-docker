<?php

namespace App\Service;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureService
{
    public function __construct(private ParameterBagInterface $params)
    {
    }

    public function square (UploadedFile $picture, ?string $folder, ?int $width = 250): string
    {
        // On donne un nouveau nom à l'image
        $file = md5(uniqid(rand(), true)) . '.webp';

        // On récupère les informations de l'image
        $pictureInfo = getimagesize($picture);
        if($pictureInfo === false) {
            throw new \Exception('Format de l\'image incorrct !');
        }
        // On vérifie le type mime
        switch($pictureInfo['mime'])
        {
            case 'image/png':
                $pictureSource = imagecreatefrompng($picture);
                break;
            case 'image/jpeg':
                $pictureSource = imagecreatefromjpeg($picture);
                break;
            case 'image/webp':
                $pictureSource = imagecreatefromwebp($picture);
                break;
            default:
                throw new \Exception('Format de l\'image incorrct !');
        }

        // On recadre l'image 
        $imageWidth = $pictureInfo[0];
        $imageHeight = $pictureInfo[1];

        switch($imageWidth <=> $imageHeight) {
            case -1:
                $squareSize = $imageWidth;
                $srcX = 0; // portrait
                $srcY = ($imageHeight - $imageWidth) / 2;
                break;

            case 0: // carré
                $squareSize = $imageWidth;
                $srcX = 0;
                $srcY = 0   ;
                break;

            case 1: // paysage
                $squareSize = $imageHeight;
                $srcX = ($imageWidth- $imageHeight) / 2;
                $srcY = 0;
                break;
            
        }

        // On crée une nouvelle image vierge
        $resizedPicture = imagecreatetruecolor($width, $width);

        // On génére le contenu de l'image
        imagecopyresampled($resizedPicture, $pictureSource, 0, 0, $srcX, $srcY, $width, $width, $squareSize, $squareSize);

        // On crée le chemin de stockage
        $path = $this->params->get('uploads_directory') . $folder;

       
        // On crée le dossier s'il n'éxiste pas
        if(!file_exists($path . '/mini/')) {
            mkdir($path . '/mini/' , 0755, true);
        }

        // On stock l'image réduite
        imagewebp($resizedPicture, $path . '/mini/' . $width . 'x' . $width . '-' . $file);

        // On stock l'image original
        $picture->move($path . '/', $file);

        return $file;
    } 
}