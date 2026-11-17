<?php

namespace App\Models;

use App\Core\Database;

class LandingPageManager
{
    /**
     * Сгенерировать HTML страницы из настроек.
     *
     * @param string $pageSlug
     * @return string
     */
    public function generate(string $pageSlug = 'home'): string
    {
        $db = Database::connection();
        $stmt = $db->query("SELECT * FROM landing_page_settings WHERE is_active = 1 ORDER BY field_group ASC, display_order ASC");
        $settings = $stmt->fetchAll();

        // Группируем по секциям
        $sections = [];
        foreach ($settings as $setting) {
            $sections[$setting['field_group']][] = $setting;
        }

        $html = '<!DOCTYPE html>' . PHP_EOL;
        $html .= '<html lang="ru">' . PHP_EOL;
        $html .= '<head>' . PHP_EOL;
        $html .= '    <meta charset="UTF-8">' . PHP_EOL;
        $html .= '    <meta name="viewport" content="width=device-width, initial-scale=1.0">' . PHP_EOL;
        $html .= '    <title>' . $this->escape($this->getFieldValue($sections, 'hero', 'title', 'Мой Сайт')) . '</title>' . PHP_EOL;
        $html .= '    <meta name="description" content="' . $this->escape($this->getFieldValue($sections, 'hero', 'subtitle', '')) . '">' . PHP_EOL;
        $html .= '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">' . PHP_EOL;
        $html .= '    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">' . PHP_EOL;
        $html .= $this->getGlobalSetting($sections, 'custom_css', '') . PHP_EOL;
        $html .= '</head>' . PHP_EOL;
        $html .= '<body>' . PHP_EOL;

        // Hero секция
        if (isset($sections['hero'])) {
            $html .= $this->renderHero($sections['hero']);
        }

        // Features секция
        if (isset($sections['features'])) {
            $html .= $this->renderSection('features', $sections['features']);
        }

        // About секция
        if (isset($sections['about'])) {
            $html .= $this->renderSection('about', $sections['about']);
        }

        // Services секция
        if (isset($sections['services'])) {
            $html .= $this->renderSection('services', $sections['services']);
        }

        // Portfolio секция
        if (isset($sections['portfolio'])) {
            $html .= $this->renderSection('portfolio', $sections['portfolio']);
        }

        // Testimonials секция
        if (isset($sections['testimonials'])) {
            $html .= $this->renderSection('testimonials', $sections['testimonials']);
        }

        // Contact секция
        if (isset($sections['contact'])) {
            $html .= $this->renderContact($sections['contact']);
        }

        // Footer секция
        if (isset($sections['footer'])) {
            $html .= $this->renderFooter($sections['footer']);
        }

        $html .= PHP_EOL . '    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>' . PHP_EOL;
        $html .= $this->getGlobalSetting($sections, 'custom_js', '') . PHP_EOL;
        $html .= $this->getGlobalSetting($sections, 'google_analytics', '') . PHP_EOL;
        $html .= '</body>' . PHP_EOL;
        $html .= '</html>';

        return $html;
    }

    /**
     * Получить значение поля.
     */
    private function getFieldValue(array $sections, string $sectionKey, string $fieldName, string $default): string
    {
        if (!isset($sections[$sectionKey])) return $default;
        foreach ($sections[$sectionKey] as $field) {
            if ($field['field_name'] === $fieldName) {
                return $field['field_value'] ?: $default;
            }
        }
        return $default;
    }

    /**
     * Получить глобальную настройку.
     */
    private function getGlobalSetting(array $sections, string $fieldName, string $default): string
    {
        if (!isset($sections['global'])) return '';
        foreach ($sections['global'] as $field) {
            if ($field['field_name'] === $fieldName && !empty($field['field_value'])) {
                if ($fieldName === 'google_analytics') {
                    return '    <!-- ' . $this->escape($field['field_value']) . ' -->' . PHP_EOL . '    <script>' . PHP_EOL . '    ' . $this->escape($field['field_value']) . PHP_EOL . '    </script>' . PHP_EOL;
                }
                if ($fieldName === 'custom_css' && !empty($field['field_value'])) {
                    return '    <style>' . PHP_EOL . '    ' . $this->escape($field['field_value']) . PHP_EOL . '    </style>' . PHP_EOL;
                }
                if ($fieldName === 'custom_js' && !empty($field['field_value'])) {
                    return '    <script>' . PHP_EOL . '    ' . $this->escape($field['field_value']) . PHP_EOL . '    </script>' . PHP_EOL;
                }
                return $field['field_value'];
            }
        }
        return $default;
    }

    /**
     * Рендер Hero секции.
     */
    private function renderHero(array $fields): string
    {
        $title = $this->getFieldValue($fields, 'hero', 'title', 'Заголовок');
        $subtitle = $this->getFieldValue($fields, 'hero', 'subtitle', '');
        $ctaText = $this->getFieldValue($fields, 'hero', 'cta_text', 'Подробнее');
        $ctaUrl = $this->getFieldValue($fields, 'hero', 'cta_url', '#');
        $bgImage = $this->getFieldValue($fields, 'hero', 'background_image', '');
        $bgColor = $this->getFieldValue($fields, 'hero', 'background_color', '#ffffff');
        $bgVideo = $this->getFieldValue($fields, 'hero', 'background_video', '');

        $style = '';
        if (!empty($bgImage)) {
            $style = 'background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url(\'' . $this->escape($bgImage) . '\') center/cover no-repeat;';
        } elseif ($bgColor !== '#ffffff') {
            $style = 'background-color: ' . $this->escape($bgColor) . ';';
        }

        $html = '    <!-- Hero Section -->' . PHP_EOL;
        $html .= '    <section class="hero-section py-5" style="' . $style . 'min-height: 80vh; display: flex; align-items: center;">' . PHP_EOL;

        if (!empty($bgVideo)) {
            $html .= '        <video autoplay muted loop playsinline style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; z-index: 0;">' . PHP_EOL;
            $html .= '            <source src="' . $this->escape($bgVideo) . '" type="video/mp4">' . PHP_EOL;
            $html .= '        </video>' . PHP_EOL;
            $html .= '        <div style="position: relative; z-index: 1;" class="container">' . PHP_EOL;
        } else {
            $html .= '        <div class="container">' . PHP_EOL;
        }

        $html .= '            <div class="row justify-content-center">' . PHP_EOL;
        $html .= '                <div class="col-lg-8 text-center text-white">' . PHP_EOL;
        $html .= '                    <h1 class="display-4 fw-bold mb-4">' . $this->escape($title) . '</h1>' . PHP_EOL;
        if (!empty($subtitle)) {
            $html .= '                    <p class="lead mb-4">' . $this->escape($subtitle) . '</p>' . PHP_EOL;
        }
        if (!empty($ctaText)) {
            $html .= '                    <a href="' . $this->escape($ctaUrl) . '" class="btn btn-lg btn-orange px-5 py-3 fw-bold">' . $this->escape($ctaText) . '</a>' . PHP_EOL;
        }
        $html .= '                </div>' . PHP_EOL;
        $html .= '            </div>' . PHP_EOL;
        $html .= '        </div>' . PHP_EOL;
        $html .= '    </section>' . PHP_EOL;

        return $html;
    }

    /**
     * Рендер обычной секции.
     */
    private function renderSection(string $sectionKey, array $fields): string
    {
        $title = $this->getFieldValue($fields, $sectionKey, 'title', ucfirst($sectionKey));
        $subtitle = $this->getFieldValue($fields, $sectionKey, 'subtitle', '');

        $html = '    <!-- ' . ucfirst($sectionKey) . ' Section -->' . PHP_EOL;
        $html .= '    <section class="py-5">' . PHP_EOL;
        $html .= '        <div class="container">' . PHP_EOL;
        $html .= '            <div class="row justify-content-center">' . PHP_EOL;
        $html .= '                <div class="col-lg-8 text-center mb-5">' . PHP_EOL;
        $html .= '                    <h2 class="display-5 fw-bold mb-3">' . $this->escape($title) . '</h2>' . PHP_EOL;
        if (!empty($subtitle)) {
            $html .= '                    <p class="lead text-muted">' . $this->escape($subtitle) . '</p>' . PHP_EOL;
        }
        $html .= '                </div>' . PHP_EOL;
        $html .= '            </div>' . PHP_EOL;
        $html .= '        </div>' . PHP_EOL;
        $html .= '    </section>' . PHP_EOL;

        return $html;
    }

    /**
     * Рендер Contact секции.
     */
    private function renderContact(array $fields): string
    {
        $title = $this->getFieldValue($fields, 'contact', 'title', 'Контакты');
        $address = $this->getFieldValue($fields, 'contact', 'address', '');
        $phone = $this->getFieldValue($fields, 'contact', 'phone', '');
        $email = $this->getFieldValue($fields, 'contact', 'email', '');
        $mapEmbed = $this->getFieldValue($fields, 'contact', 'map_embed', '');

        $html = '    <!-- Contact Section -->' . PHP_EOL;
        $html .= '    <section class="py-5 bg-light">' . PHP_EOL;
        $html .= '        <div class="container">' . PHP_EOL;
        $html .= '            <div class="row justify-content-center">' . PHP_EOL;
        $html .= '                <div class="col-lg-8 text-center mb-5">' . PHP_EOL;
        $html .= '                    <h2 class="display-5 fw-bold mb-3">' . $this->escape($title) . '</h2>' . PHP_EOL;
        $html .= '                </div>' . PHP_EOL;
        $html .= '            </div>' . PHP_EOL;
        $html .= '            <div class="row g-4 justify-content-center">' . PHP_EOL;

        if (!empty($address)) {
            $html .= '                <div class="col-md-4 text-center">' . PHP_EOL;
            $html .= '                    <i class="bi bi-geo-alt fs-1 text-orange"></i>' . PHP_EOL;
            $html .= '                    <p class="mt-2">' . $this->escape($address) . '</p>' . PHP_EOL;
            $html .= '                </div>' . PHP_EOL;
        }
        if (!empty($phone)) {
            $html .= '                <div class="col-md-4 text-center">' . PHP_EOL;
            $html .= '                    <i class="bi bi-telephone fs-1 text-orange"></i>' . PHP_EOL;
            $html .= '                    <p class="mt-2"><a href="tel:' . $this->escape($phone) . '" class="text-decoration-none">' . $this->escape($phone) . '</a></p>' . PHP_EOL;
            $html .= '                </div>' . PHP_EOL;
        }
        if (!empty($email)) {
            $html .= '                <div class="col-md-4 text-center">' . PHP_EOL;
            $html .= '                    <i class="bi bi-envelope fs-1 text-orange"></i>' . PHP_EOL;
            $html .= '                    <p class="mt-2"><a href="mailto:' . $this->escape($email) . '" class="text-decoration-none">' . $this->escape($email) . '</a></p>' . PHP_EOL;
            $html .= '                </div>' . PHP_EOL;
        }

        $html .= '            </div>' . PHP_EOL;

        if (!empty($mapEmbed)) {
            $html .= '            <div class="row mt-4">' . PHP_EOL;
            $html .= '                <div class="col-12">' . PHP_EOL;
            $html .= '                    <div class="ratio ratio-16x9">' . PHP_EOL;
            $html .= '                        ' . $mapEmbed . PHP_EOL;
            $html .= '                    </div>' . PHP_EOL;
            $html .= '                </div>' . PHP_EOL;
            $html .= '            </div>' . PHP_EOL;
        }

        $html .= '        </div>' . PHP_EOL;
        $html .= '    </section>' . PHP_EOL;

        return $html;
    }

    /**
     * Рендер Footer.
     */
    private function renderFooter(array $fields): string
    {
        $copyright = $this->getFieldValue($fields, 'footer', 'copyright', '© 2024');
        $bgColor = $this->getFieldValue($fields, 'footer', 'background_color', '#333333');
        $textColor = $this->getFieldValue($fields, 'footer', 'text_color', '#ffffff');

        $html = '    <!-- Footer -->' . PHP_EOL;
        $html .= '    <footer class="py-4" style="background-color: ' . $this->escape($bgColor) . '; color: ' . $this->escape($textColor) . ';">' . PHP_EOL;
        $html .= '        <div class="container text-center">' . PHP_EOL;
        $html .= '            <p class="mb-0">' . $this->escape($copyright) . '</p>' . PHP_EOL;
        $html .= '        </div>' . PHP_EOL;
        $html .= '    </footer>' . PHP_EOL;

        return $html;
    }

    /**
     * Экранировать HTML.
     */
    private function escape(string $str): string
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}
