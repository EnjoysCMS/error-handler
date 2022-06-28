<?php

declare(strict_types=1);


namespace EnjoysCMS\ErrorHandler\Output;


use HttpSoft\Message\Response;
use Psr\Http\Message\ResponseInterface;

final class Image extends AbstractOutput implements OutputInterface
{

    public function getResponse(): ResponseInterface
    {
        $response = (new Response())
            ->withHeader('Content-Type', $this->mimeType)
        ;

        ob_start();
        $image = $this->createImage();

        switch ($this->mimeType) {
            case 'image/gif':
                imagegif($image);
                break;
            case 'image/jpeg':
                imagejpeg($image);
                break;
            case 'image/png':
                imagepng($image);
                break;
            case 'image/webp':
                imagewebp($image);
                break;
        }

        $response->getBody()->write((string)ob_get_clean());
        return $response;
    }

    private function createImage()
    {
        $type = get_class($this->error);
        $code = empty($this->error->getCode()) ? "" : "[{$this->error->getCode()}]";
        $message = $this->error->getMessage();

        $size = 200;
        $image = imagecreatetruecolor($size, $size);
        $textColor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 10, 10, "$type $code", $textColor);

        foreach (str_split($message, intval($size / 10)) as $line => $text) {
            imagestring($image, 5, 10, ($line * 18) + 28, $text, $textColor);
        }

        return $image;
    }
}
