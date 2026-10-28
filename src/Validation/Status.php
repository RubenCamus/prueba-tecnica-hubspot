<?php

namespace App\Validation;

Enum Status: string {
    case Valido = "VALIDO";
    case Invalido = "INVALIDO";
    case Omitido = "OMITIDO";
    case ErrorTemporal = "ERROR_TEMPORAL";
}
