@php
    $prefix = $isPartner ? 'partner_' : '';
    $age         = $isPartner ? ($p->partner_age ?? null)         : ($p->age ?? null);
    $gender      = $isPartner ? ($p->partner_gender ?? null)      : ($p->gender ?? null);
    $orientation = $isPartner ? ($p->partner_orientation ?? null) : ($p->orientation ?? null);
    $height      = $isPartner ? ($p->partner_height ?? null)      : ($p->height ?? null);
    $weight      = $isPartner ? ($p->partner_weight ?? null)      : ($p->weight ?? null);
    $ethnicity   = $isPartner ? ($p->partner_ethnicity ?? null)   : ($p->ethnicity ?? null);
    $nationality = $isPartner ? ($p->partner_nationality ?? null) : ($p->nationality ?? 'México');
    $tattoos     = $isPartner ? ($p->partner_tattoos ?? null)     : ($p->tattoos ?? null);
    $piercings   = $isPartner ? ($p->partner_piercings ?? null)   : ($p->piercings ?? null);
    $smokes      = $isPartner ? ($p->partner_smokes ?? null)      : ($p->smokes ?? null);
    $drinks      = $isPartner ? ($p->partner_drinks ?? null)      : ($p->drinks ?? null);
    $langs       = json_decode($isPartner ? ($p->partner_languages ?? '[]') : ($p->languages ?? '[]'), true) ?? [];

    // Campos específicos por género
    $penisSize   = !$isPartner ? ($p->penis_size ?? null)          : ($p->partner_penis_size ?? null);
    $breastSize  = !$isPartner ? ($p->breast_size ?? null)         : ($p->partner_breast_size ?? null);
    $isM = in_array($gender, ['masculino']);
    $isF = in_array($gender, ['femenino']);

    $rows = array_filter([
        'Edad'          => $age ? $age . ' años' : null,
        'Orientación'   => $orientation ? ucfirst($orientation) : null,
        'Altura'        => $height ? $height . ' cm' : null,
        'Peso'          => $weight ? $weight . ' kg' : null,
        'Etnia'         => $ethnicity ? ucfirst($ethnicity) : null,
        'Nacionalidad'  => $nationality ?? null,
        $isM ? 'Long. del pene' : null  => $isM && $penisSize ? $penisSize . ' cm' : null,
        $isF ? 'Talla de pecho' : null  => $isF && $breastSize ? $breastSize : null,
        'Tatuajes'      => $tattoos ? ucfirst($tattoos) : null,
        'Piercings'     => $piercings ? ucfirst($piercings) : null,
        'Fuma'          => $smokes ? ucfirst($smokes) : null,
        'Bebe alcohol'  => $drinks ? ucfirst($drinks) : null,
        'Habla'         => !empty($langs) ? implode(', ', $langs) : null,
    ]);
@endphp

<table class="prf-table">
  @foreach($rows as $label => $value)
  @if($label && $value)
  <tr>
    <td>{{ $label }}:</td>
    <td>{{ $value }}</td>
  </tr>
  @endif
  @endforeach
</table>
