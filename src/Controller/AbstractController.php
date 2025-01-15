<?php

namespace JDS\Controller;

use JDS\Http\Request;
use JDS\Http\Response;
use Psr\Container\ContainerInterface;


abstract class AbstractController
{
	protected ?ContainerInterface $container = null;
	protected Request $request;
	public function setContainer(ContainerInterface $container): void
	{
		$this->container = $container;
	}

	public function setRequest(Request $request): void
	{
		$this->request = $request;
	}

	public function render(string $template, array $parameters= [], Response $response = null):
	Response
	{
        header('Cache-Control: no-cache, no-store, must-revalidate', true);
        header('Pragma: no-cache');
        header('Expires: 0');

        $content = $this->container->get('twig')->render($template, $parameters);

		$response ??= new Response();

		$response->setContent($content);

		return $response;
	}

    protected function handleImageUpload(string $images='pictures', int $numFiles=20): array {
        $imageInfos = [];
        if (count($_FILES[$images]["error"]) > $numFiles) {
            throw new \Exception('Too many files!');
        }

        foreach ($_FILES[$images]["error"] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                $img_extension = $this->getImageExtensionByType($_FILES[$images]["type"][$key]);
                $tmp_name = $_FILES[$images]["tmp_name"][$key];
                if ($this->checkFileUploadName($_FILES[$images]["name"][$key])) {
                    $imageInfo = $this->processImage($tmp_name, $img_extension);
                    $imageInfos[] = $imageInfo;
                }
            }
        }
        return $imageInfos;
    }

    private function checkFileUploadName($filename): bool
    {
        return (bool)preg_match("`^[-0-9A-Z_.]+$`i", $filename);
    }

    private function getImageExtensionByType($imageType): string {
        switch ($imageType) {
            case 'image/png':
                return "png";
            case 'image/jpeg':
            case 'image/jpg':
                return "jpg";
            case 'application/octet-stream':
                return "heic";
            default:
                return "webp";
        }
    }

    private function processImage($tmp_name, $img_extension): array {
        $storePath = "media/gallery";
        $new_filename = uniqid('gallery-', false);
        $imageUrl = "$storePath/$new_filename.webp";
        $thumbnailUrl = "$storePath/$new_filename" . "_thumbnail.webp";
        move_uploaded_file($tmp_name, "$storePath/$new_filename.$img_extension");
        if ($img_extension !== "webp") {
            $this->convertImage("$storePath/$new_filename.$img_extension", "$storePath/$new_filename.webp");
            unlink("$storePath/$new_filename.$img_extension");
        }
        return ['imageUrl' => $imageUrl, 'thumbnailUrl' => $thumbnailUrl];
    }


    private function convertImage($filename, $outfile): void
    {
        $thumbnailPath = str_replace(".webp", "_thumbnail.webp", $outfile);
        // Run the conversion command
        exec("magick $filename $outfile");
        exec("magick $filename -thumbnail 150x150 $thumbnailPath");
    }

}

