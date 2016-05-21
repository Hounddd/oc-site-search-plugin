<?php
namespace OFFLINE\SiteSearch\Classes\Providers;

use Illuminate\Database\Eloquent\Collection;
use OFFLINE\SiteSearch\Models\Settings;
use RainLab\Pages\Classes\Page;
use RainLab\Translate\Classes\Translator;

/**
 * Searches the contents generated by the
 * Rainlab.Pages plugin
 *
 * @package OFFLINE\SiteSearch\Classes\Providers
 */
class RainlabPagesResultsProvider extends ResultsProvider
{
    /**
     * Runs the search for this provider.
     *
     * @return ResultsProvider
     */
    public function search()
    {
        if ( ! $this->isInstalledAndEnabled()) {
            return $this;
        }

        foreach ($this->pages() as $page) {
            // Make this result more relevant, if the query is found in the title
            $relevance = $this->containsQuery($page->viewBag['title']) ? 2 : 1;

            $this->addResult($page->viewBag['title'], $page->parsedMarkup, $this->getUrl($page), $relevance);
        }

        return $this;
    }

    /**
     * Get all pages with matching title or content.
     *
     * @return Collection
     */
    protected function pages()
    {
        $pages = Page::all()->filter(function ($page) {
            return $this->containsQuery($page->parsedMarkup)
            || $this->containsQuery($page->viewBag['title']);
        });

        return $pages;
    }

    /**
     * Checks if the RainLab.Pages Plugin is installed and
     * enabled in the config.
     *
     * @return bool
     */
    protected function isInstalledAndEnabled()
    {
        return $this->isPluginAvailable('Rainlab.Pages')
        && Settings::get('rainlab_pages_enabled', true);
    }

    /**
     * Checks if $subjects contains the query string.
     *
     * @param $subject
     *
     * @return bool
     */
    protected function containsQuery($subject)
    {
        return mb_strpos(strtolower($subject), strtolower($this->query)) !== false;
    }

    /**
     * Display name for this provider.
     *
     * @return string
     */
    public function displayName()
    {
        return Settings::get('rainlab_pages_label', 'Page');
    }

    /**
     * Get the page's (translated) url.
     *
     * @param $page
     *
     * @return string
     */
    protected function getUrl($page)
    {
        $langPrefix = $this->translator ? $this->translator->getLocale() : '';

        return $langPrefix . $page->viewBag['url'];
    }

    /**
     * Returns the plugin's identifier string.
     *
     * @return string
     */
    public function identifier()
    {
        return 'RainLab.Pages';
    }
}

