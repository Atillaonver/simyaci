<?php
namespace Opencart\Catalog\Controller\Google;
class Google extends \Opencart\System\Engine\Controller {
    public function index(): string {
        $html = '';

        switch ($this->config->get('analytics_google_type')) {
            case 0:
                $html = $this->getGlobalSiteTag($this->config->get('analytics_google_measurement_id'));
                break;
            case 1:
                $html = $this->getGlobalSiteTag($this->config->get('analytics_google_tracking_id'));
                break;
            case 2:
                $html = $this->config->get('analytics_google_code');
                break;
        }

        return html_entity_decode($html, ENT_QUOTES, 'UTF-8');
    }

    private function getGlobalSiteTag($id): string {
        $html = '<!-- Global site tag (gtag.js) - Google Analytics -->' . "\n";
        $html .= '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $id . '"></script>' . "\n";
        $html .= '<script>' . "\n";
        $html .= 'window.dataLayer = window.dataLayer || [];' . "\n";
        $html .= ' function gtag(){dataLayer.push(arguments);}' . "\n";
        $html .= ' gtag(\'js\', new Date());' . "\n\n";
        $html .= ' gtag(\'config\', \'' . $id . '\');' . "\n";
        $html .= ' </script>' . "\n";

        return $html;
    }
}