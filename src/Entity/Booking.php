<?php

namespace App\Entity;

use DateTime;
use App\Entity\User;
use DateTimeInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\BookingRepository;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use App\Validator\Constraints as BookingAssert;
use ApiPlatform\Elasticsearch\Filter\TermFilter;
use ApiPlatform\Elasticsearch\Filter\MatchFilter;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ApiResource(description:'A booking for a room.' ,
   operations: [
    new Get(),
    new GetCollection(),
    new Post(
        name:'create_booking',
        routeName:'booking_create',
    ),
    new Put(),
    new Delete()],
    normalizationContext: ['groups' => ['booking:read']],
    denormalizationContext: ['groups' => ['booking:write']],
)]

#[BookingAssert\ValidBookingConstraint]
#[ApiFilter(TermFilter::class, properties: ['room'])]
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
    private ?DateTime $startDate = null;



    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\GreaterThan(propertyPath: 'startDate', message: 'End date must be after start date')]
    #[Assert\NotBlank(message: 'Fill in the end date')]
    #[Groups(['booking:read', 'booking:write'])]
    #[ApiFilter(DateFilter::class, properties: ['endDate'])]
    private ?DateTime $endDate = null;




    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'Fill in the room')]
    #[Groups(['booking:read', 'booking:write'])]

    private ?Room $room = null;

    #[Groups(['booking:read'])]
    private ?string $duration = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    private ?User $user = null;


    public function __construct($startDate,$endDate,$room,$user)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->room = $room;
        $this->user = $user;

    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTime $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(DateTime $endDate): static
    {
        $this->endDate = $endDate;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }


}
