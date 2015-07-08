<?php
// src/AppBundle/Entity/Video.php
namespace Model\Doctrine\Entity\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="test_doctrine_entity")
 */
class Doctrine
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $title;
    
    /**
     * @ORM\Column(type="integer", name="duration")
     */
    protected $duration;

    /**
     * @ORM\Column(type="string", name="duration_pretty")
     */
    protected $durationPretty;

    /**
     * @ORM\Column(type="boolean", name="has_embedded_subs")
     */
    protected $hasEmbeddedSubs = true;
    
    /**
     * @ORM\Column(type="string", name="embedded_subs_lang")
     */
    protected $embeddedSubsLang = 'ENG';

    /**
     * @ORM\Column(type="integer", name="year")
     */
    protected $year;
    
    /**
     * @ORM\Column(type="text", name="plot")
     */
    protected $plot;    

    /**
     * @ORM\Column(type="integer", name="season")
     */
    protected $season = null;
    
    /**
     * @ORM\Column(type="integer", name="episode")
     */
    protected $episode = null;

    /**
     * @ORM\Column(type="integer", name="like_counter")
     */
    protected $like_counter = 0;
    
    /**
     * @ORM\Column(type="integer", name="view_counter")
     */
    protected $view_counter = 0;
    
    /**
     * @ORM\Column(type="string", name="cover_photo_url", length=1024)
     */
    protected $coverPhotoUrl = '';
    
    /**
     * @ORM\OneToMany(targetEntity="VideoPrice", mappedBy="video", cascade={"all"})
     */
    protected $prices;
    
    /**
     * @ORM\OneToMany(targetEntity="UploadJournal", mappedBy="video", cascade={"all"})
     */
    protected $uploadedFiles;    
    
    /**
     * @return null
     */
    public function __construct()
    {
        $this->prices = new ArrayCollection;
        $this->uploadedFiles = new ArrayCollection;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Video
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set mimeType
     *
     * @param string $mimeType
     * @return Video
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * Get mimeType
     *
     * @return string 
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Set year
     *
     * @param integer $year
     * @return Video
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year
     *
     * @return integer 
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Set season
     *
     * @param integer $season
     * @return Video
     */
    public function setSeason($season)
    {
        $this->season = $season;

        return $this;
    }

    /**
     * Get season
     *
     * @return integer 
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * Set episode
     *
     * @param integer $episode
     * @return Video
     */
    public function setEpisode($episode)
    {
        $this->episode = $episode;

        return $this;
    }

    /**
     * Get episode
     *
     * @return integer 
     */
    public function getEpisode()
    {
        return $this->episode;
    }

    /**
     * Set duration
     *
     * @param integer $duration
     * @return Video
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get duration
     *
     * @return integer 
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set durationPretty
     *
     * @param string $durationPretty
     * @return Video
     */
    public function setDurationPretty($durationPretty)
    {
        $this->durationPretty = $durationPretty;

        return $this;
    }

    /**
     * Get durationPretty
     *
     * @return string 
     */
    public function getDurationPretty()
    {
        return $this->durationPretty;
    }

    /**
     * Set hasEmbeddedSubs
     *
     * @param boolean $hasEmbeddedSubs
     * @return Video
     */
    public function setHasEmbeddedSubs($hasEmbeddedSubs)
    {
        $this->hasEmbeddedSubs = $hasEmbeddedSubs;

        return $this;
    }

    /**
     * Get hasEmbeddedSubs
     *
     * @return boolean 
     */
    public function getHasEmbeddedSubs()
    {
        return $this->hasEmbeddedSubs;
    }

    /**
     * Set embeddedSubsLang
     *
     * @param string $embeddedSubsLang
     * @return Video
     */
    public function setEmbeddedSubsLang($embeddedSubsLang)
    {
        $this->embeddedSubsLang = $embeddedSubsLang;

        return $this;
    }

    /**
     * Get embeddedSubsLang
     *
     * @return string 
     */
    public function getEmbeddedSubsLang()
    {
        return $this->embeddedSubsLang;
    }

    /**
     * Set plot
     *
     * @param string $plot
     * @return Video
     */
    public function setPlot($plot)
    {
        $this->plot = $plot;

        return $this;
    }

    /**
     * Get plot
     *
     * @return string 
     */
    public function getPlot()
    {
        return $this->plot;
    }

    /**
     * Set like_counter
     *
     * @param integer $likeCounter
     * @return Video
     */
    public function setLikeCounter($likeCounter)
    {
        $this->like_counter = $likeCounter;

        return $this;
    }

    /**
     * Get like_counter
     *
     * @return integer 
     */
    public function getLikeCounter()
    {
        return $this->like_counter;
    }

    /**
     * Set view_counter
     *
     * @param integer $viewCounter
     * @return Video
     */
    public function setViewCounter($viewCounter)
    {
        $this->view_counter = $viewCounter;

        return $this;
    }

    /**
     * Get view_counter
     *
     * @return integer 
     */
    public function getViewCounter()
    {
        return $this->view_counter;
    }

    /**
     * Set coverPhotoUrl
     *
     * @param string $coverPhotoUrl
     * @return Video
     */
    public function setCoverPhotoUrl($coverPhotoUrl)
    {
        $this->coverPhotoUrl = $coverPhotoUrl;

        return $this;
    }

    /**
     * Get coverPhotoUrl
     *
     * @return string 
     */
    public function getCoverPhotoUrl()
    {
        return $this->coverPhotoUrl;
    }

    /**
     * Add prices
     *
     * @param \AppBundle\Entity\VideoPrice $prices
     * @return Video
     */
    public function addPrice(\AppBundle\Entity\VideoPrice $prices)
    {
        $this->prices[] = $prices;

        return $this;
    }

    /**
     * Remove prices
     *
     * @param \AppBundle\Entity\VideoPrice $prices
     */
    public function removePrice(\AppBundle\Entity\VideoPrice $prices)
    {
        $this->prices->removeElement($prices);
    }

    /**
     * Get prices
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPrices()
    {
        return $this->prices;
    }
}
