<?php
namespace BootstrapUI\View\Helper;

class HtmlHelper extends \Cake\View\Helper\HtmlHelper
{
    use OptionsAwareTrait;

    protected $_grid = [];

    protected $_gridOptions = [];

    protected $_gridCounter = 0;

    protected $_navs = [];

    /**
     * Constructor
     *
     * ### Settings
     *
     * - `templates` Either a filename to a config containing templates.
     *   Or an array of templates to load. See Cake\View\StringTemplate for
     *   template formatting.
     *
     * ### Customizing tag sets
     *
     * Using the `templates` option you can redefine the tag HtmlHelper will use.
     *
     * @param \Cake\View\View $View The View this helper is being attached to.
     * @param array $config Configuration settings for the helper.
     */
    public function __construct(\Cake\View\View $View, array $config = [])
    {
        $config['templates'] = [
            'grid' => '<div{{attrs}}>{{content}}</div>',
            'gridclass' => 'col-{{type}}-{{size}}',
            'gridoffsetclass' => 'col-{{type}}-offset-{{size}}',
        ];

        parent::__construct($View, $config);
    }

    /**
     * Returns Bootstrap badge markup. By default, uses `<SPAN>`.
     *
     * @param string $text Text to show in badge.
     * @param array $options Additional HTML attributes.
     * @return string HTML badge markup.
     */
    public function badge($text, array $options = [])
    {
        $options += ['tag' => 'span'];
        $tag = $options['tag'];
        unset($options['tag']);

        return $this->tag($tag, $text, $this->injectClasses('badge', $options));
    }

    /**
     * Returns breadcrumbs as a (x)html list
     *
     * This method uses HtmlHelper::tag() to generate list and its elements. Works
     * similar to HtmlHelper::getCrumbs(), so it uses options which every
     * crumb was added with.
     *
     * ### Options
     *
     * - `separator` Separator content to insert in between breadcrumbs, defaults to ''
     * - `firstClass` Class for wrapper tag on the first breadcrumb, defaults to 'first'
     * - `lastClass` Class for wrapper tag on current active page, defaults to 'last'
     *
     * @param array $options Array of HTML attributes to apply to the generated list elements.
     * @param string|array|bool $startText This will be the first crumb, if false it defaults to first crumb in
     *   array. Can also be an array, see `HtmlHelper::getCrumbs` for details.
     * @return string|null Breadcrumbs HTML list.
     * @link http://book.cakephp.org/3.0/en/views/helpers/html.html#creating-breadcrumb-trails-with-htmlhelper
     */
    public function getCrumbList(array $options = [], $startText = false)
    {
        $options += [
            'separator' => '',
        ];

        return parent::getCrumbList($this->injectClasses('breadcrumb', $options), $startText);
    }

    /**
     * Returns Bootstrap icon markup. By default, uses `<I>` and `glypicon`.
     *
     * @param string $name Name of icon (i.e. search, leaf, etc.).
     * @param array $options Additional HTML attributes.
     * @return string HTML icon markup.
     */
    public function icon($name, array $options = [])
    {
        $options += [
            'tag' => 'i',
            'iconSet' => 'glyphicon',
            'class' => null,
        ];

        $classes = [$options['iconSet'], $options['iconSet'] . '-' . $name];
        $options = $this->injectClasses($classes, $options);

        return $this->formatTemplate('tag', [
            'tag' => $options['tag'],
            'attrs' => $this->templater()->formatAttributes($options, ['tag', 'iconSet']),
        ]);
    }

    /**
     * Returns Bootstrap label markup. By default, uses `<SPAN>`.
     *
     * @param string $text Text to show in label.
     * @param array $options Additional HTML attributes.
     * @return string HTML icon markup.
     */
    public function label($text, $options = [])
    {
        if (is_string($options)) {
            $options = ['type' => $options];
        }

        $options += [
            'tag' => 'span',
            'type' => 'default',
        ];

        $classes = ['label', 'label-' . $options['type']];
        $tag = $options['tag'];
        unset($options['tag'], $options['type']);

        return $this->tag($tag, $text, $this->injectClasses($classes, $options));
    }

    /**
     * Set the content of a cell in a Bootstrap's grid
     *
     * @param  string $text Text, or Html content will be insert in a cell of grid
     * @param  array $options cell configuration
     * @return $this
     */
    public function grid($text = null, array $options = [])
    {
        $this->_gridCounter++;

        $this->_grid[$this->_gridCounter] = $text;

        if (!empty($options)) {
            $this->gridConfig($options);
        }

        return $this;
    }

    /**
     * Configure atribututes of a cell
     *
     * @param  array $options set the cell configuration
     * @return $this
     */
    public function gridConfig(array $options = [])
    {
        $options += [
            'type' => 'md',
            'size' => 12,
            'offset' => null, //['type'] => 'md', ['size'] => 12
        ];

        $this->_gridOptions[$this->_gridCounter][] = $options;

        return $this;
    }

    /**
     * Return html of a grid
     *
     * @return string $html of a Bootstrap grid
     */
    public function gridRender()
    {
        $items = $this->_gridItems();
        $options['class'] = 'row';

        $this->_gridCounter = 0;
        $this->_grid = $this->_gridConfig = [];

        return $this->formatTemplate('grid', [
            'attrs' => $this->templater()->formatAttributes($options),
            'content' => $items,
        ]);
    }

    /**
     * [_gridItems description]
     * @return [type] [description]
     */
    protected function _gridItems()
    {
        $out = '';

        foreach ($this->_grid as $key => $item) {
            $classes['class'] = [];
            foreach ($this->_gridOptions[$key] as $config) {
                $classes['class'][] = $this->formatTemplate('gridclass', $config);
                if (!empty($config['offset'])) {
                    $classes['class'][] = $this->formatTemplate('gridoffsetclass', $config['offset']);
                }
            }
            $out .= $this->formatTemplate('grid', [
                'attrs' => $this->templater()->formatAttributes($classes),
                'content' => $item,
            ]);
        }

        return $out;
    }

    /**
     * Add content for a bootstrap nav
     *
     * @param string $title title
     * @param string $content content
     * @param string $class css class
     * @return $this
     */
    public function addNav($title, $content, $class = null)
    {
        $this->_navs[] = [
            'title' => $title,
            'content' => $content,
            'class' => $class,
        ];

        return $this;
    }

    /**
     * Get nav html content
     *
     * @param string $type nav type
     * @return string $html of a Bootstrap nav
     */
    public function getNav($type = 'tabs')
    {
        $navContent = '';
        $tabContent = '';

        foreach ($this->_navs as $nav) {
            $navContent .= $this->formatTemplate('li', [
                'content' => $nav['title'],
                'attrs' => $this->templater()->formatAttributes($nav, ['title', 'content']),
            ]);
            $contentClass = [];
            $contentClass[] = 'tab-pane';
            if (!empty($nav['class'])) {
                $contentClass[] = $nav['class'];
            }

            $tabContent .= $this->div($contentClass, $nav['content']);
        }

        $navOptions['class'] = 'nav nav-' . $type;
        $out = $this->formatTemplate('ul', [
            'content' => $navContent,
            'attrs' => $this->templater()->formatAttributes($navOptions),
        ]);
        $out .= $this->div('tab-content', $tabContent);
        $this->_navs = [];

        return $out;
    }
}
