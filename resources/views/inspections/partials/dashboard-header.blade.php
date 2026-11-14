<table class="w-full min-w-[1200px] text-left">
                    <thead>
                        <tr>
                            @if(auth()->user()->role === 'admin')
                            <th class="w-[3%] text-center">
                                <input type="checkbox" id="select-all" onchange="toggleAllCheckboxes()" class="vt-checkbox">
                            </th>
                            @endif
                            <th class="w-[14%]">Numéro de plaque</th>
                            <th class="w-[14%]">Nom Complet</th>
                            <th class="w-[7%] text-center">Cat.</th>
                            <th class="w-[11%] text-center">Statut</th>
                            <th class="w-[9%] text-center">Temps</th>
                            <th class="w-[12%] text-center">Technicien</th>
                            <th class="w-[8%] text-center">Line</th>
                            @if(auth()->user()->role === 'admin')
                            <th class="w-[22%] text-center">Actions</th>
                            @endif
                        </tr>
                    </thead>
