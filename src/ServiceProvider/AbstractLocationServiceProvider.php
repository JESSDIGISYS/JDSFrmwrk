<?php

namespace JDS\ServiceProvider;




use League\Container\Argument\Literal\ArrayArgument;
use League\Container\Container;

abstract class AbstractLocationServiceProvider implements ServiceProviderInterface
{

    public function __construct(
        private Container $container,
        private array $states = [
            'AL' => 'ALABAMA',
            'AK' => 'ALASKA',
            'AZ' => 'ARIZONA',
            'AR' => 'ARKANSAS',
            'CA' => 'CALIFORNIA',
            'CO' => 'COLORADO',
            'CT' => 'CONNECTICUT',
            'DE' => 'DELAWARE',
            'FL' => 'FLORIDA',
            'GA' => 'GEORGIA',
            'GU' => 'GUAM GU',
            'HI' => 'HAWAII',
            'ID' => 'IDAHO',
            'IL' => 'ILLINOIS',
            'IN' => 'INDIANA',
            'IA' => 'IOWA',
            'KS' => 'KANSAS',
            'KY' => 'KENTUCKY',
            'LA' => 'LOUISIANA',
            'ME' => 'MAINE',
            'MD' => 'MARYLAND',
            'MA' => 'MASSACHUSETTS',
            'MI' => 'MICHIGAN',
            'MN' => 'MINNESOTA',
            'MS' => 'MISSISSIPPI',
            'MO' => 'MISSOURI',
            'MT' => 'MONTANA',
            'NE' => 'NEBRASKA',
            'NV' => 'NEVADA',
            'NH' => 'NEW HAMPSHIRE',
            'NJ' => 'NEW JERSEY',
            'NM' => 'NEW MEXICO',
            'NY' => 'NEW YORK',
            'NC' => 'NORTH CAROLINA',
            'ND' => 'NORTH DAKOTA',
            'OH' => 'OHIO',
            'OK' => 'OKLAHOMA',
            'OR' => 'OREGON',
            'PA' => 'PENNSYLVANIA',
            'RI' => 'RHODE ISLAND',
            'SC' => 'SOUTH CAROLINA',
            'SD' => 'SOUTH DAKOTA',
            'TN' => 'TENNESSEE',
            'TX' => 'TEXAS',
            'UT' => 'UTAH',
            'VT' => 'VERMONT',
            'VA' => 'VIRGINIA',
            'WA' => 'WASHINGTON',
            'WV' => 'WEST VIRGINIA',
            'WI' => 'WISCONSIN',
            'WY' => 'WYOMING'],
    )
    {
    }


    /* ************************************ */
    /* ********* Future Expansion ********* */
    /* *** Add These to the constructor *** */
    /* ************************************ */
//    private array $countries = [],
//    private array $cities = []



    public function getStates(): array
    {
        return $this->states;
    }

    public function register(): void
    {
        $this->registerStates();
//        $this->registerCountries();
//        $this->registerCities();
    }

    private function registerStates(): void
    {
        $this->container->add('states', new ArrayArgument($this->getStates()));
    }


    /* ************************************ */
    /* ********* Future Expansion ********* */
    /* ************************************ */

//    private function registerCountries(): void
//    {
//        $this->container->add('countries', new ArrayArgument($this->getCountries()));
//    }
//
//    private function registerCities(): void
//    {
//        $this->container->add('cities', new ArrayArgument($this->getCities()));
//    }
//
//    public function getCountries(): array
//    {
//        return $this->countries;
//    }
//
//    public function getCities(): array
//    {
//        return $this->cities;
//    }
}