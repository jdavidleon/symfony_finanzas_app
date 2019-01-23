<?php
/**
 * Created by PhpStorm.
 * User: jleon
 * Date: 23/01/2019
 * Time: 4:20 PM
 */

namespace App\Util;


trait TimestampableEntity
{
    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     * @ORM\Column(
     *      name="updated_at",
     *      type="datetime"
     * )
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(
     *      name="deleted_at",
     *      type="datetime"
     * )
     */
    protected $deletedAt;

    /**
     * TimestampableEntity constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Sets createdAt.
     *
     * @param  \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns createdAt.
     *
     * @return \DateTime
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
        throw new \InvalidArgumentException("Esté valor no puede ser establecido manualmente");
    }

    /**
     * Returns updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}