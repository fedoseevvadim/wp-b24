<?php

namespace Page;

use B24\Struct;
use B24\WPForm;

class SetupPage {

    const title = "Битрикс 24";


    function __construct() {

        echo "<h2>" . self::title . "</h2>";
        $b24Form = new WPForm();

        $arrOptions = $b24Form->getOptions();

        ?>

        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

        <form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" accept-charset="utf-8">
            <input type="hidden" name="b24_crm_hidden" value="Y">

            <?php

            echo $b24Form->buildForm($arrOptions);

            ?>

            <p class="submit">
                <input type="submit" name="Submit" value="Сохранить" />
            </p>
        </form>
        <?
    }

}