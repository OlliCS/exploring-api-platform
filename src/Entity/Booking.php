<?php

namespace App\Entity;

use DateTimeInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use Assert\TimeSlotValidator;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\BookingRepository;
use ApiPlatform\Metadata\GetCollection;
use App\Validation\Constraints\RoomValidator;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ApiResource(description:'A booking for a room.' ,
   operations: [
    new Get(),
    new GetCollection(),
    new Post(),
    new Put(),
    new Delete()],
    normalizationContext: ['groups' => ['booking:read']],
    denormalizationContext: ['groups' => ['booking:write']],
)]


class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'Fill in the start date')]
    #[Assert\GreaterThan('now', message: 'Start date must be in the future')]

    #[Groups(['booking:read', 'booking:write'])]
    #[ApiFilter(DateFilter::class, properties: ['startDate'])]
    private ?DateTimeInterface $startDate = null;



    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan(propertyPath: 'startDate', message: 'End date must be after start date')]
    #[Assert\NotBlank(message: 'Fill in the end date')]
    #[Groups(['booking:read', 'booking:write'])]
    #[ApiFilter(DateFilter::class, properties: ['endDate'])]
    private ?DateTimeInterface $endDate = null;




    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'Fill in the room')]
    #[Groups(['booking:read', 'booking:write'])]

    private ?Room $room = null;

    #[Groups(['booking:read'])]
    private ?string $duration = null;


    public function __construct($startDate,$endDate,$room)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->room = $room;

    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate->setTimezone(new \DateTimeZone('Europe/Amsterdam'));
    }

    public function setStartDate(DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate->setTimezone(new \DateTimeZone('Europe/Amsterdam'));
        return $this;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate->setTimezone(new \DateTimeZone('Europe/Amsterdam'));
    }

    public function setEndDate(DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate->setTimezone(new \DateTimeZone('Europe/Amsterdam'));

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;

        return $this;
    }

    public function getDuration(): ?string
    {
        $this->duration = $this->getStartDate()->diff($this->getEndDate())->format('%h hours %i minutes');
        return $this->duration;
    }

    public function __toString(): string
    {
        return $this->getRoom()->getName();
    }


}
