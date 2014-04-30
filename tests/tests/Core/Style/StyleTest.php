<?

class StyleTest extends \PHPUnit_Framework_TestCase {

    /*
    public static function tearDownAfterClass() {
        @unlink(dirname(__FILE__) . '/fixtures/testing.css');
        @unlink(dirname(__FILE__) . '/fixtures/cache/css/testing/styles.css');
        @rmdir(dirname(__FILE__) . '/fixtures/cache/css/testing');
        @rmdir(dirname(__FILE__) . '/fixtures/cache/css');
        @rmdir(dirname(__FILE__) . '/fixtures/cache');

        @unlink(DIR_PACKAGES . '/tester/themes/testerson/styles.css');
        @rmdir(DIR_PACKAGES . '/tester/themes/testerson');
        @rmdir(DIR_PACKAGES . '/tester/themes');
        @rmdir(DIR_PACKAGES . '/tester');
    }
    */

    public function testStyles() {
        $definition = dirname(__FILE__) . '/fixtures/styles.xml';
        $styleList = \Concrete\Core\StyleCustomizer\StyleList::loadFromXMLFile($definition);
        $sets = $styleList->getSets();
        $styles = $sets[0]->getStyles();
        $styles2 = $sets[2]->getStyles();

        $this->assertTrue($styleList instanceof \Concrete\Core\StyleCustomizer\StyleList);
        $this->assertTrue(count($styleList->getSets()) == 3);
        $this->assertTrue($sets[2]->getName() == 'Spacing');
        $this->assertTrue($styles[0]->getVariable() == 'background-color');
        $this->assertTrue($styles[1]->getVariable() == 'top-header-bar-color');
        $this->assertTrue($styles[0]->getName() == 'Background');
        $this->assertTrue($styles[1]->getName() == 'Top Header Bar');

        $this->assertTrue($styles[0] instanceof \Concrete\Core\StyleCustomizer\Style\ColorStyle);
        $this->assertTrue($styles2[0] instanceof \Concrete\Core\StyleCustomizer\Style\SizeStyle);

        $this->assertTrue($styles[0]->getFormElementPath() == DIR_FILES_ELEMENTS_CORE . '/' . DIRNAME_STYLE_CUSTOMIZER . '/' . DIRNAME_STYLE_CUSTOMIZER_TYPES . '/color.php', sprintf('Incorrect path: %s', $styles[0]->getFormElementPath()));
        $this->assertTrue($styles2[0]->getFormElementPath() == DIR_FILES_ELEMENTS_CORE . '/' . DIRNAME_STYLE_CUSTOMIZER . '/' . DIRNAME_STYLE_CUSTOMIZER_TYPES . '/size.php', sprintf('Incorrect path: %s', $styles2[0]->getFormElementPath()));
    }

    public function testLessVariableColorParsing() {
        $defaults = dirname(__FILE__) . '/fixtures/defaults.less';
        $list = \Concrete\Core\StyleCustomizer\Style\ValueList::loadFromLessFile($defaults);

        $cs1 = new \Concrete\Core\StyleCustomizer\Style\ColorStyle();
        $cs1->setVariable('header-background');
        $cs2 = new \Concrete\Core\StyleCustomizer\Style\ColorStyle();
        $cs2->setVariable('header-nav');
        $cs3 = new \Concrete\Core\StyleCustomizer\Style\ColorStyle();
        $cs3->setVariable('body-font');
        $cs4 = new \Concrete\Core\StyleCustomizer\Style\ColorStyle();
        $cs4->setVariable('body-background');

        $value1 = $cs1->getValueFromList($list);
        $value2 = $cs2->getValueFromList($list);
        $value3 = $cs3->getValueFromList($list);
        $value4 = $cs4->getValueFromList($list);

        $this->assertTrue($value1->getRed() == 255 && $value1->getGreen() == 0 && $value1->getBlue() == 0 && $value1->hasAlpha() && $value1->getAlpha() == 0.5);
        $this->assertTrue($value2->getRed() == 238 && $value2->getGreen() == 238 && $value2->getBlue() == 238 && !$value2->hasAlpha());
        $this->assertTrue($value3->getRed() == 0 && $value3->getGreen() == 0 && $value3->getBlue() == 0 && $value3->hasAlpha());
        $this->assertTrue($value4->getRed() == 255 && $value4->getGreen() == 255 && $value4->getBlue() == 255 && !$value4->hasAlpha());
    }

    public function testLessVariableSizeParsing() {
        $defaults = dirname(__FILE__) . '/fixtures/defaults.less';
        $list = \Concrete\Core\StyleCustomizer\Style\ValueList::loadFromLessFile($defaults);

        $ss1 = new \Concrete\Core\StyleCustomizer\Style\SizeStyle();
        $ss1->setVariable('bottom-margin');
        $ss2 = new \Concrete\Core\StyleCustomizer\Style\SizeStyle();
        $ss2->setVariable('leading-paragraph-spacing');

        $value1 = $ss1->getValueFromList($list);
        $value2 = $ss2->getValueFromList($list);

        $this->assertTrue($value1->getSize() == '20' && $value1->getUnits() == 'px');
        $this->assertTrue($value2->getSize() == '1.5' && $value2->getUnits() == 'em');
    }

    public function testLessVariableFontFullParsing() {
        $defaults = dirname(__FILE__) . '/fixtures/defaults.less';
        $list = \Concrete\Core\StyleCustomizer\Style\ValueList::loadFromLessFile($defaults);

        $fs1 = new \Concrete\Core\StyleCustomizer\Style\TypeStyle();
        $fs1->setVariable('header-one');
        $value1 = $fs1->getValueFromList($list);
        $this->assertTrue($value1->getFontFamily() == 'Helvetica Neue');
        $this->assertTrue($value1->getFontWeight() == 'normal');
        $this->assertTrue($value1->getTextDecoration() == 'none');
        $this->assertTrue($value1->getTextTransform() == 'uppercase');
        $this->assertTrue($value1->getFontStyle() == 'italic');

        $this->assertTrue($value1->getColor() instanceof \Concrete\Core\StyleCustomizer\Style\Value\ColorValue);
        $c1 = $value1->getColor();
        $this->assertTrue($c1->getRed() == 51 && $c1->getGreen() == 51 && $c1->getBlue() == 51 && !$c1->hasAlpha());

        $this->assertTrue($value1->getFontSize() instanceof \Concrete\Core\StyleCustomizer\Style\Value\SizeValue);
        $this->assertTrue($value1->getLineHeight() instanceof \Concrete\Core\StyleCustomizer\Style\Value\SizeValue);
        $this->assertTrue($value1->getLetterSpacing() instanceof \Concrete\Core\StyleCustomizer\Style\Value\SizeValue);
        $s1 = $value1->getFontSize();
        $s2 = $value1->getLineHeight();
        $s3 = $value1->getLetterSpacing();
        $this->assertTrue($s1->getSize() == 16 && $s1->getUnits() == 'px');
        $this->assertTrue($s2->getSize() == 24 && $s2->getUnits() == 'px');
        $this->assertTrue($s3->getSize() == 0.5 && $s3->getUnits() == 'em');
    }

    public function testLessVariableFontPartialParsing() {
        $defaults = dirname(__FILE__) . '/fixtures/defaults.less';
        $list = \Concrete\Core\StyleCustomizer\Style\ValueList::loadFromLessFile($defaults);

        $ts = new \Concrete\Core\StyleCustomizer\Style\TypeStyle();
        $ts->setVariable('body');
        $value = $ts->getValueFromList($list);
        $this->assertTrue($value->getFontFamily() == 'Arial');
        $this->assertTrue($value->getFontWeight() == 'normal');
        $this->assertTrue($value->getTextDecoration() == 'none');
        $this->assertTrue($value->getTextTransform() == 'none');
    }

public function testLessVariableImages() {
        $defaults = dirname(__FILE__) . '/fixtures/defaults.less';
        $list = \Concrete\Core\StyleCustomizer\Style\ValueList::loadFromLessFile($defaults);

        $ts = new \Concrete\Core\StyleCustomizer\Style\ImageStyle();
        $ts->setVariable('header-background');
        $value = $ts->getValueFromList($list);
        $this->assertTrue($value->getUrl() == 'images/logo.png');

    }

}
