<?php

namespace App\Entity;

use DateTimeZone;
use DateTimeImmutable;
use DateTimeInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\BookingRepository;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ApiResource(description:'A booking for a room.' ,
   operations: [
    new Get(),
    new GetCollection(),
    new Post(),
    new Put(),
    //new Patch(),
    new Delete()]
)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[ApiFilter(DateFilter::class, properties: ['start_date'])]
    private ?DateTimeInterface $start_date = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[ApiFilter(DateFilter::class, properties: ['end_date'])]
    private ?DateTimeInterface $end_date = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;



    public function __construct($start_date,$end_date,$room)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->room = $room;

    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?DateTimeInterface
    {
        return $this->start_date->setTimezone(new DateTimeZone('Europe/Amsterdam'));
    }

    public function setStartDate(DateTimeInterface $start_date): static
    {
        $this->start_date = $start_date->setTimezone(new DateTimeZone('Europe/Amsterdam'));

        return $this;
    }

    public function getEndDate(): ?DateTimeInterface
    {
        return $this->end_date->setTimezone(new DateTimeZone('Europe/Amsterdam'));
    }

    public function setEndDate(DateTimeInterface $end_date): static
    {
        $this->end_date = $end_date->setTimezone(new DateTimeZone('Europe/Amsterdam'));

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

    public function __toString(): string
    {
        return $this->getRoom()->getName();
    }


}
