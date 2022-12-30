<?php

namespace Easypage\Models;

use Easypage\Kernel\Abstractions\Model;

class PageModel extends Model
{
    protected static string $repository = 'pages';
    protected array $persistent = [
        'name',
        'title',
        'description',
        'keywords',
        'heading_text',
        'heading_links',
        'main_text',
        'description_line_1',
        'description_line_2',
        'description_line_3',
        'main_links',
        'copyright_text',
        'color_text',
        'color_link',
        'color_link_hover',
        'color_background',
        'color_bg_overlay',
        'color_bg_overlay_opacity',
        'font_logo',
        'font_headings',
        'font_text',
        'image_background',
        'image_favicon',
    ];

    protected array $updateable = [
        'name',
        'title',
        'description',
        'keywords',
        'heading_text',
        'heading_links',
        'main_text',
        'description_line_1',
        'description_line_2',
        'description_line_3',
        'main_links',
        'copyright_text',
        'color_text',
        'color_link',
        'color_link_hover',
        'color_background',
        'color_bg_overlay',
        'color_bg_overlay_opacity',
        'font_logo',
        'font_headings',
        'font_text',
        'image_background',
        'image_favicon',
    ];

    const FONTS_LIST = [
        'Roboto',
        'Saira',
        'Montserrat',
        'Chivo Mono',
        'Unbounded',
        'Open Sans',
        'Noto Sans',
        'Noto Sans Japanese',
        'Lato',
        'Exo 2',
        'Poppins',
        'Rubik Gemstones',
        'Roboto Condensed',
        'Rubik Vinyl',
        'Ubuntu',
        'Roboto Slab',
        'PT Sans',
        'Noto Sans Korean',
        'Playfair Display',
        'Lora',
        'Fira Sans',
        'PT Serif',
        'Rubik Puddles',
        'Dancing Script',
        'Sevillana',
        'Cinzel'
    ];

    protected string $name = '';
    protected string $title = '';
    protected string $description = '';
    protected string $keywords = '';
    protected string $heading_text = '';
    protected array $heading_links = [];
    protected string $main_text = '';
    protected string $description_line_1 = '';
    protected string $description_line_2 = '';
    protected string $description_line_3 = '';
    protected array $main_links = [];
    protected string $copyright_text = '';
    protected string $color_text = '';
    protected string $color_link = '';
    protected string $color_link_hover = '';
    protected ?string $color_background;
    protected ?string $color_bg_overlay;
    protected ?string $color_bg_overlay_opacity;
    protected ?string $font_logo;
    protected ?string $font_headings;
    protected ?string $font_text;
    protected ?string $image_background;
    protected ?string $image_favicon;

    protected function validate(): bool
    {
        $this->_is_valid = true;
        $this->_validator_messages = [];

        if ($this->validateProperty('name', 'hasPresence', invalidMessage: "Page name cannot be blank")) {
            $this->validateProperty('name', 'hasLength', args: ['min' => 4, 'max' => 255], invalidMessage: "Page name must be between 4 and 255 characters");
        }

        if ($this->validateProperty('title', 'hasPresence', invalidMessage: "Title cannot be blank")) {
            $this->validateProperty('title', 'hasLength', args: ['min' => 4, 'max' => 255], invalidMessage: "Title must be between 4 and 255 characters");
        }

        $this->validateProperty('description', 'hasLengthLessThan', args: ['max' => 255], invalidMessage: "Value must be less than 255 characters");

        $this->validateProperty('keywords', 'hasLengthLessThan', args: ['max' => 255], invalidMessage: "Value must be less than 255 characters");

        $this->validateProperty('heading_text', 'hasLengthLessThan', args: ['max' => 125], invalidMessage: "Value must be less than 125 characters");

        $this->validateProperty('heading_links', 'hasArraysOf', args: ['scheme' => ['name', 'href']], invalidMessage: "Heading links are filled wrong");

        $this->validateProperty('main_text', 'hasLengthLessThan', args: ['max' => 125], invalidMessage: "Value must be less than 125 characters");

        $this->validateProperty('description_line_1', 'hasLengthLessThan', args: ['max' => 255], invalidMessage: "Value must be less than 255 characters");

        $this->validateProperty('description_line_2', 'hasLengthLessThan', args: ['max' => 255], invalidMessage: "Value must be less than 255 characters");

        $this->validateProperty('main_links', 'hasArraysOf', args: ['scheme' => ['name', 'href']], invalidMessage: "Main links are filled wrong");

        $this->validateProperty('copyright_text', 'hasLengthLessThan', args: ['max' => 255], invalidMessage: "Value must be less than 255 characters");

        return $this->_is_valid;
    }
}
