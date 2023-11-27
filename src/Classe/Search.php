<?php

namespace App\Classe;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Entity\Famille;
use App\Entity\Marque;
use App\Entity\Calibre;

class Search
{
    /**
     * @var string
     */

    public string $string;

    /**
     * @var int
     */

    public int $minprice;
        /**
     * @var int
     */

    public int $maxprice;

     /**
     * @var string
     */

     public string $orderPrices;
     
        /**
     * @var bool
     */

     public bool $isOccasion;

         /**
     * @var bool
     */

     public bool $isPromo;

        /**
     * @var bool
     */

     public bool $isFDO;

    /**
     * @var Calibre[]
     */

     public array $calibre = [];
    /**
     * @var Category[]
     */
    public array $categories = [];
    /** 
    * @var SubCategory[]
    */
   public array $subCategories = [];
        /**
     * @var Famille[]
     */
    public array $famille = [];
    /**
     * @var Marque[]
     */
    public array $marques = [];

    public function __toString()
    {
        return '';
    }
}
