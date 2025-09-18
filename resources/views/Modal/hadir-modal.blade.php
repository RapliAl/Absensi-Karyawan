<div style="text-align: center; padding: 20px;">
    <h3 style="margin-bottom: 10px; color: #333;">{{ $karyawan->{'nama'} }}</h3>
    <p style="margin-bottom: 20px; color: #666; font-size: 16px;">
        Nama: <strong style="color: #007bff;">{{ $karyawan->{'nama'} }}</strong>
    </p>
    
    {{-- Tampilan Modal Karyawan --}}
    <div style="display: flex; justify-content: center; margin: 20px 0;">
        <div style="padding: 15px; background: white; border: 2px solid #e0e0e0; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            {{-- <img  
                 alt="QR  {{ $karyawan->name }}" 
                 style="display: block; max-width: 200px; height: auto;"
                 onerror="this.style.display='none'; document.getElementById('qr-fallback').style.display='block';"> --}}
        </div>
    </div>
</div>
