<?php

declare(strict_types=1);

namespace App\Providers\PhotoStorage\Local;

use App\Dto\Booking;
use App\Entity\Photo;
use App\Providers\PhotoStorage\PhotoStorageInterface;
use App\Repository\PhotoRepository;

class Local implements PhotoStorageInterface
{
    public function __construct(
        private readonly string $targetDirectory,
        private readonly string $photosUrl,
        private readonly PhotoRepository $photoRepository,
    ) {
    }

    public function put(Booking $booking, string $filepath): Photo
    {
        $newFileName = md5_file($filepath) . '.' . pathinfo($filepath, PATHINFO_EXTENSION);
        $newFilePath = "{$booking->checkOutDate}/{$booking->orderId}/$newFileName";
        $relativePath = "{$this->targetDirectory}/$newFilePath";
        $this->createDirectory($relativePath);
        copy($filepath, $relativePath);

        $photo = (new Photo())
            ->setUrl("{$this->photosUrl}/$newFilePath")
            ->setPath($relativePath)
        ;

        $this->photoRepository->save($photo, true);

        return $photo;
    }

    public function remove(Photo $photo): void
    {
        $path = $photo->getPath();
        unlink($path);
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $isDirEmpty = !(new \FilesystemIterator($dir))->valid();
        if ($isDirEmpty) {
            rmdir($dir);
        }
        $this->photoRepository->remove($photo, true);
    }

    private function createDirectory(string $path): void
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
