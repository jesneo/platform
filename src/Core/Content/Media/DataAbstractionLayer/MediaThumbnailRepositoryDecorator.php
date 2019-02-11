<?php declare(strict_types=1);

namespace Shopware\Core\Content\Media\DataAbstractionLayer;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Shopware\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailCollection;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregatorResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MediaThumbnailRepositoryDecorator implements EntityRepositoryInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var EntityRepositoryInterface
     */
    private $innerRepo;

    public function __construct(
        EntityRepositoryInterface $innerRepo,
        EventDispatcherInterface $eventDispatcher,
        UrlGeneratorInterface $urlGenerator,
        FilesystemInterface $filesystem
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->urlGenerator = $urlGenerator;
        $this->filesystem = $filesystem;
        $this->innerRepo = $innerRepo;
    }

    public function delete(array $ids, Context $context): EntityWrittenContainerEvent
    {
        $thumbnails = $this->getThumbnailsByIds($ids, $context);

        return $this->deleteFromCollection($thumbnails, $context);
    }

    // Unchanged methods

    public function aggregate(Criteria $criteria, Context $context): AggregatorResult
    {
        return $this->innerRepo->aggregate($criteria, $context);
    }

    public function searchIds(Criteria $criteria, Context $context): IdSearchResult
    {
        return $this->innerRepo->searchIds($criteria, $context);
    }

    public function clone(string $id, Context $context, string $newId = null): EntityWrittenContainerEvent
    {
        return $this->innerRepo->clone($id, $context, $newId);
    }

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        return $this->innerRepo->search($criteria, $context);
    }

    public function update(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->innerRepo->update($data, $context);
    }

    public function upsert(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->innerRepo->upsert($data, $context);
    }

    public function create(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->innerRepo->create($data, $context);
    }

    public function createVersion(string $id, Context $context, ?string $name = null, ?string $versionId = null): string
    {
        return $this->innerRepo->createVersion($id, $context, $name, $versionId);
    }

    public function merge(string $versionId, Context $context): void
    {
        $this->innerRepo->merge($versionId, $context);
    }

    private function getThumbnailsByIds(array $ids, Context $context): MediaThumbnailCollection
    {
        $criteria = new Criteria();
        $criteria->addAssociation('media_thumbnail.media');
        $criteria->addFilter(new EqualsAnyFilter('media_thumbnail.id', $ids));

        $thumbnailsSearch = $this->search($criteria, $context);

        /** @var MediaThumbnailCollection $thumbnails */
        $thumbnails = $thumbnailsSearch->getEntities();

        return $thumbnails;
    }

    private function deleteFromCollection(MediaThumbnailCollection $thumbnails, Context $context): EntityWrittenContainerEvent
    {
        if ($thumbnails->count() === 0) {
            $event = EntityWrittenContainerEvent::createWithDeletedEvents([], $context, []);
            $this->eventDispatcher->dispatch(EntityWrittenContainerEvent::NAME, $event);

            return $event;
        }

        $thumbnailIds = [];
        foreach ($thumbnails as $thumbnail) {
            $thumbnailIds[] = [
                'id' => $thumbnail->getId(),
            ];

            $relatedMedia = $thumbnail->getMedia();

            $thumbnailPath = $this->urlGenerator->getRelativeThumbnailUrl(
                $relatedMedia,
                $thumbnail->getWidth(),
                $thumbnail->getHeight()
            );

            try {
                $this->filesystem->delete($thumbnailPath);
            } catch (FileNotFoundException $e) {
                //ignore file is already deleted
            }
        }

        return $this->innerRepo->delete($thumbnailIds, $context);
    }
}