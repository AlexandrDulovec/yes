<?php
namespace App\Presenters;

use Nette;
use App\Model\PostFacade;

class HomePresenter extends Nette\Application\UI\Presenter
{
    public function __construct(
        private PostFacade $postFacade,
    ) {
    }

    public function renderDefault(int $page = 1): void
    {
        // Zjistíme si celkový počet publikovaných článků
        $articlesCount = $this->postFacade->getPublishedArticlesCount();

        // Vyrobíme si instanci Paginatoru a nastavíme jej
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($articlesCount); // celkový počet článků
        $paginator->setItemsPerPage(8); // počet položek na stránce
        $paginator->setPage($page); // číslo aktuální stránky

        // Z databáze si vytáhneme omezenou množinu článků podle výpočtu Paginatoru
        $articles = $this->postFacade->getPublicArticles($paginator->getLength(), $paginator->getOffset());

        // kterou předáme do šablony
        $this->template->articles = $articles;
        // a také samotný Paginator pro zobrazení možností stránkování
        $this->template->paginator = $paginator;
    }
}

