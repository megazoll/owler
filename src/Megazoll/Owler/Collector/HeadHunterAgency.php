<?php

namespace Megazoll\Owler\Collector;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class HeadHunterAgency
{
    const START_URL = '/agenciesratings.mvc';
    const URL_PREFIX = 'http://hh.ru';

    private $client;

    public function __construct()
    {
        $this->client = new Client;
    }

    public function collect()
    {
        $categories = $this->getCategories();
        $companies = [];
        foreach ($categories as $categoryId) {
            $companies = array_merge($companies, $this->collectCategory($categoryId));
        }

        return $companies;
    }

    private function getCategories()
    {
        $crawler = $this->client->request('GET', static::URL_PREFIX.static::START_URL);
        
        return $crawler->filter('.b-karating-kalist')->filter('.b-karating-kalist-item')->each(function (\DOMElement $node) {
            $crawler = new Crawler;
            $crawler->add($node);

            return str_replace('?professionalArea=', '', $crawler->filter('a')->attr('href'));
        });
    }

    private function collectCategory($category)
    {
        $pages = $this->getPageCount($category);

        $companies = [];
        for ($i = 0; $i < $pages; $i++) {
            $companies = array_merge($companies, $this->collectPage($category, $i));
        }

        return $companies;
    }

    private function getPageCount($category)
    {
        $crawler = $this->client->request('GET', static::URL_PREFIX.static::START_URL.'?professionalArea='.$category);
        $pager = $crawler->filter('.b-pager');

        return $pager->count() ? $pager->filter('a')->last()->text() : 1;
    }

    public function collectPage($category, $page = 0)
    {
        $crawler = $this->client->request('GET', static::URL_PREFIX.static::START_URL.'?professionalArea='.$category.'&page='.$page);

        return $crawler->filter('.b-karating-tr')->each(function (\DOMElement $node) {
            $crawler = new Crawler;
            $crawler->add($node);

            return [
                'title'     => $crawler->filter('.b-karating-td-name')->filter('a')->text(),
                'vacancies' => (int) $crawler->filter('.b-karating-td-data')->filter('strong')->text()
            ];
        });
    }
}
