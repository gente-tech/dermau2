<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* modules/custom/derma_menu/templates/derma-menu-block.html.twig */
class __TwigTemplate_1996d8936f83eabb696e7bc98d2b0062 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 1
        yield "<nav class=\"du-header__nav\">
  <ul class=\"du-header__nav-list\" id=\"duNavList\">

    ";
        // line 4
        $context['_parent'] = $context;
        $context['_seq'] = CoreExtension::ensureTraversable(($context["items"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["element"]) {
            // line 5
            yield "      ";
            $context["url"] = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["element"], "link", [], "any", false, false, true, 5), "getUrlObject", [], "method", false, false, true, 5);
            // line 6
            yield "      ";
            $context["title"] = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["element"], "link", [], "any", false, false, true, 6), "getTitle", [], "method", false, false, true, 6);
            // line 7
            yield "
      <li class=\"du-header__nav-item\">

        <a href=\"";
            // line 10
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["url"] ?? null), "toString", [], "method", false, false, true, 10), "html", null, true);
            yield "\"
           class=\"du-header__nav-link ";
            // line 11
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["element"], "in_active_trail", [], "any", false, false, true, 11)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("du-header__nav-link--active") : ("")));
            yield "\">
          ";
            // line 12
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
            yield "
        </a>

        ";
            // line 15
            if ((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["element"], "subtree", [], "any", false, false, true, 15)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) {
                // line 16
                yield "          <ul class=\"du-header__nav-dropdown\">
            ";
                // line 17
                $context['_parent'] = $context;
                $context['_seq'] = CoreExtension::ensureTraversable(CoreExtension::getAttribute($this->env, $this->source, $context["element"], "subtree", [], "any", false, false, true, 17));
                foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
                    // line 18
                    yield "              ";
                    $context["child_url"] = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["child"], "link", [], "any", false, false, true, 18), "getUrlObject", [], "method", false, false, true, 18);
                    // line 19
                    yield "              ";
                    $context["child_title"] = CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, $context["child"], "link", [], "any", false, false, true, 19), "getTitle", [], "method", false, false, true, 19);
                    // line 20
                    yield "
              <li>
                <a href=\"";
                    // line 22
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["child_url"] ?? null), "toString", [], "method", false, false, true, 22), "html", null, true);
                    yield "\"
                   class=\"du-header__nav-dropdown-link ";
                    // line 23
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar((((($tmp = CoreExtension::getAttribute($this->env, $this->source, $context["child"], "in_active_trail", [], "any", false, false, true, 23)) && $tmp instanceof Markup ? (string) $tmp : $tmp)) ? ("du-header__nav-dropdown-link--active") : ("")));
                    yield "\">
                  ";
                    // line 24
                    yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["child_title"] ?? null), "html", null, true);
                    yield "
                </a>
              </li>
            ";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_key'], $context['child'], $context['_parent']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 28
                yield "          </ul>
        ";
            }
            // line 30
            yield "
      </li>
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_key'], $context['element'], $context['_parent']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 33
        yield "
    <li class=\"du-header__nav-item du-header__nav-item--campus\">
      <a href=\"#\" class=\"du-btn-campus\">
        Ingresar al campus <span class=\"du-icon__next\"></span>
      </a>
    </li>

  </ul>
</nav>";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["items"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/custom/derma_menu/templates/derma-menu-block.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  127 => 33,  119 => 30,  115 => 28,  105 => 24,  101 => 23,  97 => 22,  93 => 20,  90 => 19,  87 => 18,  83 => 17,  80 => 16,  78 => 15,  72 => 12,  68 => 11,  64 => 10,  59 => 7,  56 => 6,  53 => 5,  49 => 4,  44 => 1,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/custom/derma_menu/templates/derma-menu-block.html.twig", "/var/www/html/web/modules/custom/derma_menu/templates/derma-menu-block.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["for" => 4, "set" => 5, "if" => 15];
        static $filters = ["escape" => 10];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['for', 'set', 'if'],
                ['escape'],
                [],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
