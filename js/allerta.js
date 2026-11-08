// ============================================================
// BOLLETTINO CRITICITÀ — stato allerta + tabella rischi
// ============================================================
document.addEventListener("DOMContentLoaded", function() {
    const apiUrl = new URL('get_allerta.php', window.location.href).href;
    const requestTimeout = 8000;

    const configStati = {
        'VERDE': {
            colore: 'var(--alert-verde)',
            testoColor: '#ffffff',
            icona: 'bi-check-circle-fill',
            desc: 'VERDE • NESSUNA CRITICITÀ'
        },
        'GIALLO': {
            colore: 'var(--alert-giallo)',
            testoColor: '#121E2B',
            icona: 'bi-exclamation-triangle-fill',
            desc: 'GIALLO • ORDINARIA CRITICITÀ'
        },
        'ARANCIONE': {
            colore: 'var(--alert-arancio)',
            testoColor: '#ffffff',
            icona: 'bi-exclamation-triangle-fill',
            desc: 'ARANCIONE • MODERATA CRITICITÀ'
        },
        'ARANCIO': { // alias: alcune fonti usano "ARANCIO" invece di "ARANCIONE"
            colore: 'var(--alert-arancio)',
            testoColor: '#ffffff',
            icona: 'bi-exclamation-triangle-fill',
            desc: 'ARANCIONE • MODERATA CRITICITÀ'
        },
        'ROSSO': {
            colore: 'var(--alert-rosso)',
            testoColor: '#ffffff',
            icona: 'bi-shield-fill-x',
            desc: 'ROSSO • ELEVATA CRITICITÀ'
        }
    };

    const fallbackData = {
        status: 'success',
        allerta_globale: 'VERDE',
        ultimo_aggiornamento: 'Dati non disponibili',
        dettagli: [
            { rischio: 'Dati non disponibili', oggi: 'VERDE', domani: 'VERDE' }
        ]
    };

    function fetchWithTimeout(url, options = {}, timeout = requestTimeout) {
        return Promise.race([
            fetch(url, options),
            new Promise((_, reject) => setTimeout(() => reject(new Error('Timeout richiesta API')), timeout))
        ]);
    }

    function generaBadgeTabella(stato) {
        const label = String(stato || 'VERDE').toUpperCase();
        const cfg = configStati[label] || { colore: '#ccc', testoColor: '#000' };
        const badge = document.createElement('span');
        badge.className = 'pc-badge';
        badge.style.backgroundColor = cfg.colore;
        badge.style.color = cfg.testoColor;
        badge.textContent = label;
        return badge;
    }

    function renderBollettino(data) {
        const statoGlobale = String(data.allerta_globale || 'VERDE').toUpperCase();
        const configGlobale = configStati[statoGlobale] || configStati['VERDE'];
        const containerStato = document.getElementById('bollettino-stato-container');
        const testoStato = document.getElementById('bollettino-stato-testo');
        const iconaStato = document.getElementById('bollettino-stato-icona');
        const aggiornamento = document.getElementById('bollettino-aggiornamento');
        const tbody = document.getElementById('tabella-rischi-body');

        if (containerStato) {
            containerStato.style.backgroundColor = configGlobale.colore;
            containerStato.style.color = configGlobale.testoColor;
            containerStato.setAttribute('aria-live', 'polite');
            containerStato.setAttribute('aria-atomic', 'true');
        }

        if (testoStato) {
            testoStato.textContent = configGlobale.desc;
        }

        if (iconaStato) {
            iconaStato.className = 'bi ' + configGlobale.icona;
        }

        if (aggiornamento) {
            aggiornamento.innerHTML = '';
            const label = document.createElement('span');
            label.className = 'text-secondary small d-block';
            label.textContent = 'ULTIMA SINCRONIZZAZIONE COMUNE';
            const strong = document.createElement('strong');
            strong.className = 'text-dark';
            strong.textContent = String(data.ultimo_aggiornamento || 'N/D');
            aggiornamento.append(label, strong);

            if (data.status === 'degraded') {
                const warning = document.createElement('div');
                warning.className = 'small text-warning mt-1';
                warning.textContent = 'Dati caricati dalla cache: potrebbero non essere aggiornati.';
                aggiornamento.appendChild(warning);
            }
        }

        if (!tbody) return;
        tbody.innerHTML = '';

        const dettagli = Array.isArray(data.dettagli) ? data.dettagli : [];
        if (dettagli.length === 0) {
            mostraErrore();
            return;
        }

        dettagli.forEach(item => {
            const row = document.createElement('tr');
            const tdRischio = document.createElement('td');
            tdRischio.className = 'ps-4 fw-bold text-dark';
            tdRischio.style.fontSize = '0.95rem';
            tdRischio.textContent = String(item.rischio || '—');

            const tdOggi = document.createElement('td');
            tdOggi.className = 'text-center';
            tdOggi.appendChild(generaBadgeTabella(item.oggi));

            const tdDomani = document.createElement('td');
            tdDomani.className = 'text-center';
            tdDomani.appendChild(generaBadgeTabella(item.domani));

            row.append(tdRischio, tdOggi, tdDomani);
            tbody.appendChild(row);
        });
    }

    function mostraErrore() {
        const tbody = document.getElementById('tabella-rischi-body');
        if (!tbody) return;
        tbody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center py-5 text-danger fw-bold">
                    <i class="bi bi-wifi-off fs-4 d-block mb-2" aria-hidden="true"></i>
                    Impossibile sincronizzare i dati del bollettino meteo. Riprova più tardi.
                </td>
            </tr>
        `;
    }

    fetchWithTimeout(apiUrl, { cache: 'no-store' }, requestTimeout)
        .then(response => {
            if (!response.ok) throw new Error('Errore di rete nell\'interrogare il file JSON');
            return response.json();
        })
        .then(data => {
            if (data && (data.status === 'success' || data.status === 'degraded')) {
                renderBollettino(data);
            } else {
                console.warn('Dati API non validi, uso fallback locale.');
                renderBollettino(fallbackData);
            }
        })
        .catch(error => {
            console.error('Errore API:', error);
            renderBollettino(fallbackData);
        });
});

// ============================================================
// METEO DEL TERRITORIO — condizioni attuali, orario, giornaliero
// ============================================================
document.addEventListener("DOMContentLoaded", function() {
    const widget = document.getElementById('meteo-widget');
    if (!widget) return;

    const body = document.getElementById('meteo-body');
    const fonteEl = document.getElementById('meteo-fonte');
    const requestTimeout = 8000;

    const mappaCodiciWMO = {
        0:  { icona: 'bi-sun-fill',              desc: 'Sereno' },
        1:  { icona: 'bi-cloud-sun-fill',        desc: 'Prevalentemente sereno' },
        2:  { icona: 'bi-cloud-sun-fill',        desc: 'Parzialmente nuvoloso' },
        3:  { icona: 'bi-clouds-fill',           desc: 'Coperto' },
        45: { icona: 'bi-cloud-fog2-fill',       desc: 'Nebbia' },
        48: { icona: 'bi-cloud-fog2-fill',       desc: 'Nebbia con brina' },
        51: { icona: 'bi-cloud-drizzle-fill',    desc: 'Pioviggine debole' },
        53: { icona: 'bi-cloud-drizzle-fill',    desc: 'Pioviggine moderata' },
        55: { icona: 'bi-cloud-drizzle-fill',    desc: 'Pioviggine intensa' },
        56: { icona: 'bi-cloud-drizzle-fill',    desc: 'Pioviggine gelata debole' },
        57: { icona: 'bi-cloud-drizzle-fill',    desc: 'Pioviggine gelata intensa' },
        61: { icona: 'bi-cloud-rain-fill',       desc: 'Pioggia debole' },
        63: { icona: 'bi-cloud-rain-fill',       desc: 'Pioggia moderata' },
        65: { icona: 'bi-cloud-rain-heavy-fill', desc: 'Pioggia intensa' },
        66: { icona: 'bi-cloud-rain-fill',       desc: 'Pioggia gelata debole' },
        67: { icona: 'bi-cloud-rain-heavy-fill', desc: 'Pioggia gelata intensa' },
        71: { icona: 'bi-cloud-snow-fill',       desc: 'Neve debole' },
        73: { icona: 'bi-cloud-snow-fill',       desc: 'Neve moderata' },
        75: { icona: 'bi-cloud-snow-fill',       desc: 'Neve intensa' },
        77: { icona: 'bi-cloud-snow-fill',       desc: 'Granelli di neve' },
        80: { icona: 'bi-cloud-rain-heavy-fill', desc: 'Rovesci deboli' },
        81: { icona: 'bi-cloud-rain-heavy-fill', desc: 'Rovesci moderati' },
        82: { icona: 'bi-cloud-rain-heavy-fill', desc: 'Rovesci violenti' },
        85: { icona: 'bi-cloud-snow-fill',       desc: 'Rovesci di neve deboli' },
        86: { icona: 'bi-cloud-snow-fill',       desc: 'Rovesci di neve intensi' },
        95: { icona: 'bi-cloud-lightning-fill',      desc: 'Temporale' },
        96: { icona: 'bi-cloud-lightning-rain-fill', desc: 'Temporale con grandine debole' },
        99: { icona: 'bi-cloud-lightning-rain-fill', desc: 'Temporale con grandine forte' }
    };

    const mappaSimboliMetNo = {
        clearsky:      { icona: { giorno: 'bi-sun-fill', notte: 'bi-moon-stars-fill' }, desc: 'Sereno' },
        fair:          { icona: { giorno: 'bi-cloud-sun-fill', notte: 'bi-cloud-moon-fill' }, desc: 'Prevalentemente sereno' },
        partlycloudy:  { icona: { giorno: 'bi-cloud-sun-fill', notte: 'bi-cloud-moon-fill' }, desc: 'Parzialmente nuvoloso' },
        cloudy:        { icona: 'bi-clouds-fill', desc: 'Coperto' },
        fog:           { icona: 'bi-cloud-fog2-fill', desc: 'Nebbia' },

        lightrainshowers:  { icona: 'bi-cloud-drizzle-fill', desc: 'Rovesci deboli' },
        rainshowers:       { icona: 'bi-cloud-rain-fill', desc: 'Rovesci di pioggia' },
        heavyrainshowers:  { icona: 'bi-cloud-rain-heavy-fill', desc: 'Rovesci intensi' },

        lightrainshowersandthunder: { icona: 'bi-cloud-lightning-rain-fill', desc: 'Rovesci deboli con temporale' },
        rainshowersandthunder:      { icona: 'bi-cloud-lightning-rain-fill', desc: 'Rovesci con temporale' },
        heavyrainshowersandthunder: { icona: 'bi-cloud-lightning-rain-fill', desc: 'Rovesci intensi con temporale' },

        lightsleetshowers: { icona: 'bi-cloud-sleet-fill', desc: 'Rovesci deboli di nevischio' },
        sleetshowers:      { icona: 'bi-cloud-sleet-fill', desc: 'Rovesci di nevischio' },
        heavysleetshowers: { icona: 'bi-cloud-sleet-fill', desc: 'Rovesci intensi di nevischio' },

        lightsnowshowers:  { icona: 'bi-cloud-snow-fill', desc: 'Rovesci deboli di neve' },
        snowshowers:       { icona: 'bi-cloud-snow-fill', desc: 'Rovesci di neve' },
        heavysnowshowers:  { icona: 'bi-cloud-snow-fill', desc: 'Rovesci intensi di neve' },

        lightrain: { icona: 'bi-cloud-drizzle-fill', desc: 'Pioggia debole' },
        rain:      { icona: 'bi-cloud-rain-fill', desc: 'Pioggia' },
        heavyrain: { icona: 'bi-cloud-rain-heavy-fill', desc: 'Pioggia intensa' },

        lightrainandthunder: { icona: 'bi-cloud-lightning-rain-fill', desc: 'Pioggia debole con temporale' },
        rainandthunder:      { icona: 'bi-cloud-lightning-rain-fill', desc: 'Pioggia con temporale' },
        heavyrainandthunder: { icona: 'bi-cloud-lightning-rain-fill', desc: 'Pioggia intensa con temporale' },

        sleet:       { icona: 'bi-cloud-sleet-fill', desc: 'Nevischio' },
        lightsleet:  { icona: 'bi-cloud-sleet-fill', desc: 'Nevischio debole' },
        heavysleet:  { icona: 'bi-cloud-sleet-fill', desc: 'Nevischio intenso' },

        snow:      { icona: 'bi-cloud-snow-fill', desc: 'Neve' },
        lightsnow: { icona: 'bi-cloud-snow-fill', desc: 'Neve debole' },
        heavysnow: { icona: 'bi-cloud-snow-fill', desc: 'Neve intensa' },

        lightsnowandthunder: { icona: 'bi-cloud-lightning-fill', desc: 'Neve debole con temporale' },
        snowandthunder:      { icona: 'bi-cloud-lightning-fill', desc: 'Neve con temporale' },
        heavysnowandthunder: { icona: 'bi-cloud-lightning-fill', desc: 'Neve intensa con temporale' },

        thunder: { icona: 'bi-cloud-lightning-fill', desc: 'Temporale' }
    };

    function infoCondizione(current) {
        if (current.symbol_code) {
            const notte = current.symbol_code.includes('_night');
            const base = current.symbol_code.replace(/_day|_night|_polartwilight$/, '');
            const voce = mappaSimboliMetNo[base];
            if (voce) {
                const icona = typeof voce.icona === 'object'
                    ? (notte ? voce.icona.notte : voce.icona.giorno)
                    : voce.icona;
                return { icona, desc: voce.desc };
            }
        }
        if (typeof current.weather_code === 'number') {
            return mappaCodiciWMO[current.weather_code] || { icona: 'bi-cloud-fill', desc: 'N/D' };
        }
        return { icona: 'bi-cloud-fill', desc: 'N/D' };
    }

    function puntoCardinale(gradi) {
        const punti = ['N', 'NE', 'E', 'SE', 'S', 'SO', 'O', 'NO'];
        const indice = Math.round(gradi / 45) % 8;
        return punti[indice];
    }

    function fetchWithTimeout(url, options = {}, timeout = requestTimeout) {
        return Promise.race([
            fetch(url, options),
            new Promise((_, reject) => setTimeout(() => reject(new Error('Timeout richiesta meteo')), timeout))
        ]);
    }

    function formattaOra(isoTime) {
        const parti = isoTime.split('T');
        return parti.length > 1 ? parti[1].slice(0, 5) : isoTime;
    }

    function oraCorrenteStringa() {
        const ora = new Date();
        const pad = n => String(n).padStart(2, '0');
        return `${ora.getFullYear()}-${pad(ora.getMonth() + 1)}-${pad(ora.getDate())}T${pad(ora.getHours())}`;
    }

    function mostraErroreMeteo() {
        if (!body) return;
        body.innerHTML = `
            <div class="text-center py-4 text-danger fw-bold">
                <i class="bi bi-wifi-off fs-4 d-block mb-2" aria-hidden="true"></i>
                Dati meteo non disponibili al momento.
            </div>
        `;
    }

    function costruisciAvvisi(current, daily) {
        const avvisi = [];
        const raffica = current.wind_gusts_10m;
        const pioggiaProb = Array.isArray(daily.precipitation_probability_max) ? daily.precipitation_probability_max[0] : null;
        const pioggiaAccumulo = Array.isArray(daily.precipitation_sum) ? daily.precipitation_sum[0] : null;

        if (typeof raffica === 'number' && raffica >= 50) {
            avvisi.push(`Raffiche di vento fino a ${Math.round(raffica)} km/h previste`);
        }
        if (typeof pioggiaProb === 'number' && pioggiaProb >= 70 && typeof pioggiaAccumulo === 'number' && pioggiaAccumulo >= 10) {
            avvisi.push(`Possibili piogge intense: ${pioggiaAccumulo.toFixed(1)} mm previsti oggi (${Math.round(pioggiaProb)}% probabilità)`);
        }
        return avvisi;
    }

    function renderMeteo(current, hourly, daily) {
        if (!body) return;
        const info = infoCondizione(current);

        const avvisi = costruisciAvvisi(current, daily);
        const htmlAvvisi = avvisi.map(testo => `
            <div class="meteo-avviso">
                <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                <span>${testo}</span>
            </div>
        `).join('');

        let htmlOrario = '';
        if (hourly && Array.isArray(hourly.time)) {
            const oraCorrente = oraCorrenteStringa();
            let indiceIniziale = hourly.time.findIndex(t => t.slice(0, 13) >= oraCorrente);
            if (indiceIniziale === -1) indiceIniziale = 0;

            const prossimeOre = [];
            for (let i = indiceIniziale; i < Math.min(indiceIniziale + 8, hourly.time.length); i++) {
                const infoOra = mappaCodiciWMO[hourly.weather_code[i]] || { icona: 'bi-cloud-fill', desc: 'N/D' };
                prossimeOre.push(`
                    <div class="meteo-hourly-item">
                        <div class="meteo-hourly-ora">${formattaOra(hourly.time[i])}</div>
                        <i class="bi ${infoOra.icona}" aria-hidden="true"></i>
                        <div class="meteo-hourly-temp">${Math.round(hourly.temperature_2m[i])}°</div>
                        <div class="meteo-hourly-pioggia"><i class="bi bi-droplet-fill" aria-hidden="true"></i> ${hourly.precipitation_probability[i] ?? 0}%</div>
                    </div>
                `);
            }
            htmlOrario = prossimeOre.join('');
        }

        let htmlGiornaliero = '';
        if (daily && Array.isArray(daily.time)) {
            const alba = daily.sunrise ? formattaOra(daily.sunrise[0]) : '—';
            const tramonto = daily.sunset ? formattaOra(daily.sunset[0]) : '—';
            htmlGiornaliero = `
                <div class="meteo-daily-item">
                    <i class="bi bi-thermometer-high" aria-hidden="true"></i>
                    <div>
                        <div class="meteo-valore">${Math.round(daily.temperature_2m_max[0])}° / ${Math.round(daily.temperature_2m_min[0])}°</div>
                        <div class="meteo-etichetta">Max / Min</div>
                    </div>
                </div>
                <div class="meteo-daily-item">
                    <i class="bi bi-umbrella-fill" aria-hidden="true"></i>
                    <div>
                        <div class="meteo-valore">${(daily.precipitation_sum[0] ?? 0).toFixed(1)} mm</div>
                        <div class="meteo-etichetta">Accumulo previsto</div>
                    </div>
                </div>
                <div class="meteo-daily-item">
                    <i class="bi bi-wind" aria-hidden="true"></i>
                    <div>
                        <div class="meteo-valore">${Math.round(daily.wind_gusts_10m_max[0] ?? 0)} km/h</div>
                        <div class="meteo-etichetta">Raffica massima</div>
                    </div>
                </div>
                <div class="meteo-daily-item">
                    <i class="bi bi-brightness-high-fill" aria-hidden="true"></i>
                    <div>
                        <div class="meteo-valore">${daily.uv_index_max ? Math.round(daily.uv_index_max[0]) : '—'}</div>
                        <div class="meteo-etichetta">Indice UV max</div>
                    </div>
                </div>
                <div class="meteo-daily-item">
                    <i class="bi bi-sunrise-fill" aria-hidden="true"></i>
                    <div>
                        <div class="meteo-valore">${alba}</div>
                        <div class="meteo-etichetta">Alba</div>
                    </div>
                </div>
                <div class="meteo-daily-item">
                    <i class="bi bi-sunset-fill" aria-hidden="true"></i>
                    <div>
                        <div class="meteo-valore">${tramonto}</div>
                        <div class="meteo-etichetta">Tramonto</div>
                    </div>
                </div>
            `;
        }

        const riquadri = [];

        if (typeof current.apparent_temperature === 'number') {
            riquadri.push(`
                <div class="meteo-item">
                    <i class="bi bi-thermometer-half" aria-hidden="true"></i>
                    <div class="meteo-valore">${Math.round(current.apparent_temperature)}°C</div>
                    <div class="meteo-etichetta">Percepiti</div>
                </div>
            `);
        }

        riquadri.push(`
            <div class="meteo-item">
                <i class="bi bi-droplet-half" aria-hidden="true"></i>
                <div class="meteo-valore">${Math.round(current.relative_humidity_2m ?? 0)}%</div>
                <div class="meteo-etichetta">Umidità</div>
            </div>
        `);

        riquadri.push(`
            <div class="meteo-item">
                <i class="bi bi-wind" aria-hidden="true"></i>
                <div class="meteo-valore">${Math.round(current.wind_speed_10m ?? 0)} km/h</div>
                <div class="meteo-valore-secondario">${typeof current.wind_gusts_10m === 'number' ? 'Raffiche ' + Math.round(current.wind_gusts_10m) + ' km/h' : ''}</div>
                <div class="meteo-etichetta">Vento${typeof current.wind_direction_10m === 'number' ? ' ' + puntoCardinale(current.wind_direction_10m) : ''}</div>
            </div>
        `);

        riquadri.push(`
            <div class="meteo-item">
                <i class="bi bi-speedometer2" aria-hidden="true"></i>
                <div class="meteo-valore">${Math.round(current.surface_pressure ?? 0)} hPa</div>
                <div class="meteo-etichetta">Pressione</div>
            </div>
        `);

        riquadri.push(`
            <div class="meteo-item">
                <i class="bi bi-clouds" aria-hidden="true"></i>
                <div class="meteo-valore">${Math.round(current.cloud_cover ?? 0)}%</div>
                <div class="meteo-etichetta">Nuvolosità</div>
            </div>
        `);

        if (typeof current.precipitation_next_hour === 'number') {
            riquadri.push(`
                <div class="meteo-item">
                    <i class="bi bi-cloud-rain" aria-hidden="true"></i>
                    <div class="meteo-valore">${current.precipitation_next_hour} mm</div>
                    <div class="meteo-etichetta">Pioggia prossima ora</div>
                </div>
            `);
        } else if (typeof current.precipitation === 'number') {
            riquadri.push(`
                <div class="meteo-item">
                    <i class="bi bi-cloud-rain" aria-hidden="true"></i>
                    <div class="meteo-valore">${current.precipitation} mm</div>
                    <div class="meteo-etichetta">Pioggia in corso</div>
                </div>
            `);
        }

        body.innerHTML = `
            ${htmlAvvisi}
            <div class="meteo-headline">
                <i class="bi ${info.icona} meteo-icona-principale" aria-hidden="true"></i>
                <div>
                    <div class="meteo-temp-principale">${Math.round(current.temperature_2m)}°C</div>
                    <div class="meteo-desc">${info.desc}</div>
                </div>
            </div>
            <div class="meteo-grid">${riquadri.join('')}</div>
            ${htmlOrario ? `
                <div class="meteo-sezione-titolo"><i class="bi bi-clock-history" aria-hidden="true"></i> Prossime ore</div>
                <div class="meteo-hourly-scroll">${htmlOrario}</div>
            ` : ''}
            ${htmlGiornaliero ? `
                <div class="meteo-sezione-titolo"><i class="bi bi-calendar-day" aria-hidden="true"></i> Riepilogo giornata</div>
                <div class="meteo-daily-grid">${htmlGiornaliero}</div>
            ` : ''}
        `;
    }

    function caricaMeteo(lat, lng, citta) {
        const url = new URL('get_meteo.php', window.location.href);
        if (lat && lng) {
            url.searchParams.set('lat', lat);
            url.searchParams.set('lng', lng);
        } else if (citta) {
            url.searchParams.set('city', citta);
        } else {
            mostraErroreMeteo();
            return;
        }

        fetchWithTimeout(url.toString())
            .then(res => {
                if (!res.ok) throw new Error('Errore di rete nel proxy meteo');
                return res.json();
            })
            .then(data => {
                if (data && data.current) {
                    renderMeteo(data.current, data.hourly, data.daily);
                } else {
                    mostraErroreMeteo();
                }
            })
            .catch(err => {
                console.error('Errore meteo:', err);
                mostraErroreMeteo();
            });
    }

    function caricaTemporale(lat, lng, citta) {
        const url = new URL('get_temporale.php', window.location.href);
        if (lat && lng) {
            url.searchParams.set('lat', lat);
            url.searchParams.set('lng', lng);
        } else if (citta) {
            url.searchParams.set('city', citta);
        } else {
            return;
        }

        fetchWithTimeout(url.toString())
            .then(res => res.ok ? res.json() : null)
            .then(data => {
                if (!data || !body) return;
                if (data.livello === 'temporale' || data.livello === 'pioggia') {
                    const avviso = document.createElement('div');
                    avviso.className = 'meteo-avviso';
                    if (data.livello === 'temporale') {
                        avviso.style.backgroundColor = 'rgba(220, 53, 69, 0.12)';
                        avviso.style.borderColor = 'rgba(220, 53, 69, 0.4)';
                        avviso.style.color = '#7a1a24';
                        avviso.innerHTML = '<i class="bi bi-cloud-lightning-fill" aria-hidden="true"></i><span>Temporale rilevato dal radar in questo momento</span>';
                    } else {
                        avviso.innerHTML = '<i class="bi bi-cloud-rain-fill" aria-hidden="true"></i><span>Pioggia in corso rilevata dal radar</span>';
                    }
                    body.prepend(avviso);
                }
            })
            .catch(() => { /* non bloccante: se fallisce, semplicemente non mostriamo il badge */ });
    }

    const lat = widget.dataset.lat;
    const lng = widget.dataset.lng;
    const citta = widget.dataset.city;

    caricaMeteo(lat, lng, citta);
    caricaTemporale(lat, lng, citta);
});

// ============================================================
// RADAR PRECIPITAZIONI — mappa Leaflet con lazy-load
// ============================================================
document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.getElementById('radar-toggle');
    const container = document.getElementById('radar-container');
    const iconaToggle = document.getElementById('radar-toggle-icona');
    if (!toggleBtn || !container) return;

    let leafletCaricato = false;
    let mapInizializzata = false;
    let map, layerAttuale;
    let framePassati = [];
    let colorSchemeId = 0;
    let host = '';
    let indiceAnimazione = 0;
    let animazioneTimer = null;

    function caricaScript(src) {
        return new Promise((resolve, reject) => {
            const s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }

    function caricaCss(href) {
        const l = document.createElement('link');
        l.rel = 'stylesheet';
        l.href = href;
        document.head.appendChild(l);
    }

    function urlTile(frame) {
        return `${host}${frame.path}/256/{z}/{x}/{y}/${colorSchemeId}/1_1.png`;
    }

    function mostraFrame(indice) {
        if (!framePassati[indice]) return;
        const frame = framePassati[indice];
        const nuovoLayer = L.tileLayer(urlTile(frame), { opacity: 0.7 });
        nuovoLayer.addTo(map);
        if (layerAttuale) {
            const vecchio = layerAttuale;
            nuovoLayer.once('load', () => map.removeLayer(vecchio));
        }
        layerAttuale = nuovoLayer;

        const orarioEl = document.getElementById('radar-orario');
        if (orarioEl) {
            const data = new Date(frame.time * 1000);
            orarioEl.textContent = 'Ultimo aggiornamento: ' + data.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
        }
    }

    function inizializzaRadar() {
        const lat = parseFloat(container.dataset.lat) || 41.9;
        const lng = parseFloat(container.dataset.lng) || 12.5;
        const citta = container.dataset.city || '';

        map = L.map('radar-map').setView([lat, lng], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 12,
        }).addTo(map);

        // Marker sul paese
        L.marker([lat, lng])
            .addTo(map)
            .bindPopup(citta || 'Centro comune')
            .openPopup();

        fetch(new URL('get_radar.php', window.location.href))
            .then(res => res.json())
            .then(data => {
                host = data.host;
                framePassati = data.frame_passati || [];

                const schemi = data.color_schemes || [];
                const schemaColorato = schemi.find(s => /rainbow/i.test(s.name)) || schemi.find(s => s.id === 7) || schemi[0];
                colorSchemeId = schemaColorato ? schemaColorato.id : 7;

                if (framePassati.length > 0) {
                    indiceAnimazione = framePassati.length - 1;
                    mostraFrame(indiceAnimazione);
                }
            })
            .catch(() => {
                const orarioEl = document.getElementById('radar-orario');
                if (orarioEl) orarioEl.textContent = 'Radar non disponibile al momento.';
            });

        mapInizializzata = true;
    }

    const playBtn = document.getElementById('radar-play');
    if (playBtn) {
        playBtn.addEventListener('click', () => {
            if (animazioneTimer) {
                clearInterval(animazioneTimer);
                animazioneTimer = null;
                playBtn.innerHTML = '<i class="bi bi-play-fill" aria-hidden="true"></i> Riproduci ultima ora';
                return;
            }
            playBtn.innerHTML = '<i class="bi bi-pause-fill" aria-hidden="true"></i> Pausa';
            indiceAnimazione = 0;
            animazioneTimer = setInterval(() => {
                mostraFrame(indiceAnimazione);
                indiceAnimazione = (indiceAnimazione + 1) % framePassati.length;
            }, 600);
        });
    }

    toggleBtn.addEventListener('click', () => {
        const aperto = container.style.display !== 'none';
        container.style.display = aperto ? 'none' : 'block';
        toggleBtn.setAttribute('aria-expanded', String(!aperto));
        iconaToggle.className = 'bi fs-4 ' + (aperto ? 'bi-chevron-down' : 'bi-chevron-up');

        if (!aperto && !leafletCaricato) {
            caricaCss('https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css');
            caricaScript('https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js')
                .then(() => {
                    leafletCaricato = true;
                    inizializzaRadar();
                })
                .catch(() => {
                    container.innerHTML = '<p class="text-danger">Impossibile caricare la mappa radar.</p>';
                });
        } else if (!aperto && mapInizializzata) {
            setTimeout(() => map.invalidateSize(), 200);
        }
    });
});