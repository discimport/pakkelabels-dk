<?php
/**
 * Pakkelabels
 *
 * PHP version 5
 *
 * @category  Pakkelabels
 * @package   Pakkelabels
 * @author    Lars Olesen <lars@intraface.dk>
 * @copyright 2015 Lars Olesen
 * @license   MIT Open Source License https://opensource.org/licenses/MIT
 * @version   GIT: <git_id>
 * @link      http://github.com/discimport/pakkelabels-dk
 */

namespace Pakkelabels\Exception;

/**
 * Class Pakkelabels_Exception
 *
 * @category  Pakkelabels
 * @package   Pakkelabels
 * @author    Lars Olesen <lars@intraface.dk>
 * @copyright 2015 Lars Olesen
 * @license   http://opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/discimport/pakkelabels-dk
 */
class Pakkelabels_Exception extends \Exception
{
    /**
     * Construct the exception.
     *
     * @param string $message Message returned by API for error.
     */
    public function __construct($message = null)
    {
        if (is_array($message)) {
            parent::__construct(implode(', ', $message));
        } else {
            parent::__construct($message);
        }
    }
}
