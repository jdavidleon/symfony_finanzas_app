<?php
/**
 * Created by PhpStorm.
 * User: jleon
 * Date: 23/01/2019
 * Time: 4:20 PM
 */

namespace App\Util;


use DateTime;
use InvalidArgumentException;
use Gedmo\Mapping\Annotation as Gedmo;

trait TimestampAbleEntity
{
    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deletedAt;

    public function __construct()
    {
        $this->createdAt = new DateTime('now');
    }

    /**
     * Sets createdAt.
     *
     * @param  DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns createdAt.
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets updatedAt.
     *
     * @return void
     */
    private function setUpdatedAt()
    {
        throw new InvalidArgumentException("Este valor no puede ser establecido manualmente");
    }

    /**
     * Returns updatedAt.
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return DateTime
     */
    public function getDeletedAt(): DateTime
    {
        return $this->deletedAt;
    }

    /**
     * @param DateTime $deletedAt
     */
    public function setDeletedAt(DateTime $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }
}