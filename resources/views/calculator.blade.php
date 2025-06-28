<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kalkulator Akademik Terpadu - Telkom University</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        // Customizing Tailwind CSS with the specified color palette
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'telkom-dark-red': '#B6252A',
                        'telkom-bright-red': '#ED1E28',
                        'telkom-dark-gray': '#55565B',
                        'telkom-light-gray': '#959597',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .info-table th, .info-table td { padding: 8px 12px; border: 1px solid #e5e7eb; text-align: left; }
        .info-table th { background-color: #f9fafb; }
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center; z-index: 50; }
        .modal-content { background: white; padding: 2rem; border-radius: 1rem; max-width: 90%; width: 500px; max-height: 90vh; overflow-y: auto; }
        .spinner { border: 4px solid rgba(0, 0, 0, 0.1); width: 36px; height: 36px; border-radius: 50%; border-left-color: #B6252A; animation: spin 1s ease infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="bg-gray-100 text-black">

    <div class="container mx-auto p-4 md:p-8 max-w-7xl">
        <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
            
            <div class="flex flex-col sm:flex-row items-center text-center sm:text-left gap-4 sm:gap-6 mb-8">
                <img class="h-16 w-auto" src="https://smb.telkomuniversity.ac.id/wp-content/uploads/2023/03/Logo-Utama-Telkom-University.png" alt="Logo Universitas Telkom" onerror="this.onerror=null;this.src='https://placehold.co/200x50/f0f0f0/333?text=Logo+Tel-U';">
                <div class="sm:border-l-2 sm:border-gray-200 sm:pl-6">
                    <h1 class="text-2xl md:text-3xl font-bold text-black">Kalkulator Akademik Terpadu</h1>
                    <p class="mt-1 md:mt-2 text-telkom-dark-gray">Hitung IPK, cek syarat Bahasa Inggris, dan dapatkan saran AI.</p>
                </div>
            </div>

            <div class="border-t border-b border-gray-200 py-6 my-6">
                <h2 class="text-lg font-semibold text-black mb-4">Informasi Mahasiswa & Syarat</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="student-name" class="block mb-2 text-sm font-medium text-telkom-dark-gray">Nama Mahasiswa</label>
                        <input type="text" id="student-name" class="bg-gray-50 border-gray-300 text-black text-sm rounded-lg focus:ring-telkom-dark-red focus:border-telkom-dark-red block w-full p-2.5" placeholder="Masukkan nama Anda">
                    </div>
                    <div>
                        <label for="student-nim" class="block mb-2 text-sm font-medium text-telkom-dark-gray">NIM</label>
                        <input type="text" id="student-nim" class="bg-gray-50 border-gray-300 text-black text-sm rounded-lg focus:ring-telkom-dark-red focus:border-telkom-dark-red block w-full p-2.5" placeholder="Masukkan NIM Anda">
                    </div>
                    <div>
                        <label for="program-level" class="block mb-2 text-sm font-medium text-telkom-dark-gray">Pilih Jenjang Pendidikan</label>
                        <select id="program-level" class="bg-gray-50 border-gray-300 text-black text-sm rounded-lg focus:ring-telkom-dark-red focus:border-telkom-dark-red block w-full p-2.5">
                            <option value="s1" selected>Program Diploma Tiga, Sarjana/Sarjana Terapan</option>
                            <option value="s2">Program Magister/Magister Terapan</option>
                            <option value="s3">Program Doktor/Doktor Terapan</option>
                        </select>
                    </div>
                    <div>
                        <label for="english-score" class="block mb-2 text-sm font-medium text-telkom-dark-gray">Skor TOEFL/EPrT</label>
                         <input type="number" id="english-score" class="bg-gray-50 border-gray-300 text-black text-sm rounded-lg focus:ring-telkom-dark-red focus:border-telkom-dark-red block w-full p-2.5" placeholder="Masukkan skor Anda">
                          <div id="english-score-result" class="text-xs mt-2 h-4"></div>
                    </div>
                </div>
            </div>

            <h3 class="text-lg font-semibold text-black mb-4">Perhitungan IPK</h3>
            <div class="lg:grid lg:grid-cols-5 lg:gap-12">
                <div class="lg:col-span-3">
                    <div id="courses-list" class="grid grid-cols-1 lg:grid-cols-2 gap-4"></div>
                    <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-start">
                        <button onclick="addCourse()" class="flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-telkom-dark-red hover:bg-telkom-bright-red rounded-lg transition-colors">
                           <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>Tambah Mata Kuliah
                        </button>
                    </div>
                </div>

                <div class="lg:col-span-2 mt-10 lg:mt-0">
                    <div class="lg:sticky lg:top-8">
                        <div id="result-container" class="text-center bg-gray-50 p-6 rounded-xl border border-gray-200">
                            <h2 class="text-lg font-medium text-telkom-dark-gray">Indeks Prestasi Kumulatif (IPK)</h2>
                            <p id="result" class="text-5xl font-bold text-telkom-dark-red my-2">0.00</p>
                            <p id="predicate-result" class="text-lg font-semibold text-telkom-bright-red capitalize"></p>
                            <p id="summary" class="text-sm text-telkom-light-gray mt-2">Hasil akan muncul di sini.</p>
                            <button id="get-ai-feedback-btn" onclick="getAIFeedback()" class="mt-4 w-full flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-white bg-telkom-dark-gray hover:bg-black rounded-lg transition-colors">
                                ✨ Dapatkan Umpan Balik & Saran AI
                            </button>
                        </div>
                        
                        <div class="mt-8 text-xs text-telkom-light-gray space-y-4">
                            <div id="conversion-table-container">
                                <p class="font-semibold text-sm text-black mb-2">Panduan Konversi Nilai</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-center">
                                    <div class="p-2 bg-gray-100 rounded-md"><p class="font-bold text-black">A</p><p class="text-telkom-dark-gray">85 &lt; NSM</p></div>
                                    <div class="p-2 bg-gray-100 rounded-md"><p class="font-bold text-black">AB</p><p class="text-telkom-dark-gray">75 - 85</p></div>
                                    <div class="p-2 bg-gray-100 rounded-md"><p class="font-bold text-black">B</p><p class="text-telkom-dark-gray">65 - 75</p></div>
                                    <div class="p-2 bg-gray-100 rounded-md"><p class="font-bold text-black">BC</p><p class="text-telkom-dark-gray">60 - 65</p></div>
                                    <div class="p-2 bg-gray-100 rounded-md"><p class="font-bold text-black">C</p><p class="text-telkom-dark-gray">50 - 60</p></div>
                                    <div class="p-2 bg-gray-100 rounded-md"><p class="font-bold text-black">D</p><p class="text-telkom-dark-gray">40 - 50</p></div>
                                    <div class="p-2 bg-gray-100 rounded-md col-span-2 md:col-span-1"><p class="font-bold text-black">E</p><p class="text-telkom-dark-gray">&le; 40</p></div>
                                </div>
                            </div>
                            <div id="predicate-s1" class="predicate-table pt-4 border-t border-gray-200"><p class="font-semibold text-sm text-black mb-2">Predikat Kelulusan (D3/S1)</p><table class="w-full text-xs info-table"><thead><tr><th>Predikat</th><th>IPK</th><th>Masa Studi</th></tr></thead><tbody><tr><td>Sempurna</td><td>3,91 – 4,00</td><td>≤ 8 smt</td></tr><tr><td>Dengan Pujian</td><td>3,51 – 3,90</td><td>≤ 8 smt</td></tr><tr><td>Sangat Memuaskan</td><td>3,51 – 4,00</td><td>&gt; 8 smt</td></tr><tr><td>Memuaskan</td><td>2,76 – 3,50</td><td>Tanpa syarat</td></tr></tbody></table></div>
                            <div id="predicate-s2" class="predicate-table pt-4 border-t border-gray-200" style="display:none;"><p class="font-semibold text-sm text-black mb-2">Predikat Kelulusan (S2)</p><table class="w-full text-xs info-table"><thead><tr><th>Predikat</th><th>IPK</th><th>Masa Studi</th></tr></thead><tbody><tr><td>Sempurna</td><td>3,96 – 4,00</td><td>≤ 4 smt</td></tr><tr><td>Dengan Pujian</td><td>3,76 – 3,95</td><td>≤ 4 smt</td></tr><tr><td>Sangat Memuaskan</td><td>3,76 – 4,00</td><td>&gt; 4 smt</td></tr><tr><td>Memuaskan</td><td>3,26 – 3,75</td><td>Tanpa syarat</td></tr></tbody></table></div>
                            <div id="predicate-s3" class="predicate-table pt-4 border-t border-gray-200" style="display:none;"><p class="font-semibold text-sm text-black mb-2">Predikat Kelulusan (S3)</p><table class="w-full text-xs info-table"><thead><tr><th>Predikat</th><th>IPK</th><th>Masa Studi</th></tr></thead><tbody><tr><td>Sempurna</td><td>3,96 – 4,00</td><td>≤ 6 smt</td></tr><tr><td>Dengan Pujian</td><td>3,76 – 3,95</td><td>≤ 8 smt</td></tr><tr><td>Sangat Memuaskan</td><td>3,76 – 4,00</td><td>&gt; 8 smt</td></tr><tr><td>Memuaskan</td><td>3,26 – 3,75</td><td>Tanpa syarat</td></tr></tbody></table></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-6 text-sm text-telkom-light-gray">
        © 2025. Created with ❤️ by Rachdian
    </footer>
    
    <div id="ai-feedback-modal" class="modal-overlay" style="display: none;"><div class="modal-content"><div class="flex justify-between items-center mb-4"><h3 class="text-xl font-bold text-black">✨ Umpan Balik & Saran AI</h3><button onclick="closeModal()" class="text-gray-400 hover:text-gray-700">&times;</button></div><div id="modal-body"><div id="modal-loader" class="flex flex-col items-center justify-center"><div class="spinner"></div><p class="mt-2 text-sm text-telkom-light-gray">Sedang menganalisis performa Anda...</p></div><div id="modal-response" class="text-telkom-dark-gray leading-relaxed" style="display: none;"></div></div></div></div>

    <script src="{{ asset('js/calculator.js') }}"></script>
</body>
</html>
