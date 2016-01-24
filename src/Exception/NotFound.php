<?php
namespace Idealogica\InDI\Exception;

use Interop\Container\Exception;

class NotFound extends Container implements Exception\NotFoundException {}