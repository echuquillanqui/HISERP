@forelse($referrals as $referral)
    <tr>
        <td class="ps-4">
            <div class="fw-bold text-dark">{{ $referral->referral_code }}</div>
            <div class="text-muted small"><i class="bi bi-calendar3 me-1"></i>{{ $referral->created_at->format('d/m/Y H:i') }}</div>
        </td>
        <td>
            <div class="fw-bold">{{ strtoupper($referral->patient->surname . ' ' . $referral->patient->last_name . ', ' . $referral->patient->first_name) }}</div>
            <div class="text-secondary small">DNI: {{ $referral->patient->dni }}</div>
        </td>
        <td>
            @php $ins = strtoupper($referral->patient->insurance_type ?? 'PARTICULAR'); @endphp
            <span class="badge {{ $ins == 'SIS' ? 'bg-success' : ($ins == 'ESSALUD' ? 'bg-primary' : 'bg-secondary') }}">
                {{ $ins }}
            </span>
        </td>
        <td>
            <div class="small fw-bold text-truncate" style="max-width: 250px;">
                <i class="bi bi-hospital me-1 text-primary"></i>{{ $referral->destination_facility }}
            </div>
            <div class="text-muted x-small">Origen: {{ $referral->origin_facility }}</div>
        </td>
        <td class="text-end pe-4">
            <div class="btn-group">
                <a href="{{ route('referrals.edit', $referral->id) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                    <i class="bi bi-pencil-square"></i>
                </a>
                
                @if($ins == 'SIS')
                    <a href="{{ route('referrals.pdf', $referral->id) }}" class="btn btn-sm btn-success" target="_blank" title="Imprimir SIS">
                        <i class="bi bi-file-pdf"></i>
                    </a>
                @else
                    <a href="{{ route('referrals.pdf_essalud', $referral->id) }}" class="btn btn-sm btn-primary" target="_blank" title="Imprimir EsSalud">
                        <i class="bi bi-file-pdf"></i>
                    </a>
                @endif

                <form action="{{ route('referrals.destroy', $referral->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar esta referencia?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                        <i class="bi bi-trash"></i>
                    </button>
                </form>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="text-center py-5">
            <div class="text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                No se encontraron referencias con los filtros aplicados.
            </div>
        </td>
    </tr>
@endforelse