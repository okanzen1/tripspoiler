 <div x-data="reviewModal()"
     x-on:open-review.window="open=true; success=false; name=''; email=''; comment=''; rating=0; hover=0;" x-show="open"
     x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">

     <div @click.away="open=false"
         class="w-full max-w-xl rounded-[32px] bg-white shadow-[0_30px_80px_rgba(0,0,0,0.2)] p-10 relative">

         <button @click="open=false"
             class="absolute right-6 top-6 text-slate-400 hover:text-black text-xl cursor-pointer">
             ✕
         </button>


         <!-- SUCCESS SCREEN -->

         <template x-if="success">

             <div class="text-center py-10">

                 <div class="flex justify-center mb-6">

                     <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center">

                         <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-green-600" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">

                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />

                         </svg>

                     </div>

                 </div>

                 <h3 class="text-2xl font-bold text-slate-900">
                     {{ __('reviews.success.title') }}
                 </h3>

                 <p class="text-slate-500 mt-2">
                     {{ __('reviews.success.description') }}
                 </p>

             </div>

         </template>



         <!-- FORM -->

         <template x-if="!success">

             <form @submit.prevent="submitReview" class="space-y-6">
                 <div x-show="error" x-text="error"
                     class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3">
                 </div>

                 <input type="hidden" name="source" value="{{ $source }}">
                 <input type="hidden" name="source_id" value="{{ $sourceId }}">

                 <div class="hidden">
                     <input type="text" x-model="website">
                 </div>


                 <h3 class="text-2xl font-bold text-slate-900 text-center">
                     {{ __('reviews.form.title') }}
                 </h3>


                 <div>

                     <label class="text-sm font-medium text-slate-700">
                         {{ __('reviews.form.name') }}
                     </label>

                     <input type="text" x-model="name" required
                         class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-[#c62e2e] focus:ring-0">

                 </div>


                 <div>

                     <label class="text-sm font-medium text-slate-700">
                         {{ __('reviews.form.email') }}
                     </label>

                     <input type="email" x-model="email" required
                         class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-[#c62e2e] focus:ring-0">

                 </div>



                 <div>

                     <label class="text-sm font-medium text-slate-700">
                         {{ __('reviews.form.rating') }}
                     </label>

                     <div class="flex gap-2 mt-3 text-3xl">

                         <template x-for="i in 5">

                             <span @click="rating=i" @mouseenter="hover=i" @mouseleave="hover=0"
                                 class="cursor-pointer transition"
                                 :class="(hover >= i || rating >= i) ? 'text-amber-400 scale-110' : 'text-gray-300'">

                                 ★
                             </span>

                         </template>

                     </div>

                 </div>



                 <div>

                     <label class="text-sm font-medium text-slate-700">
                         {{ __('reviews.form.comment') }}
                     </label>

                     <textarea x-model="comment" rows="4" required
                         class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-[#c62e2e] focus:ring-0 resize-none"></textarea>

                 </div>


                 <button type="submit" :disabled="loading"
                     class="w-full rounded-full bg-[#c62e2e] py-4 text-white font-semibold text-lg hover:bg-[#b92626] transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">

                     <span x-show="!loading">{{ __('reviews.form.submit') }}</span>
                     <span x-show="loading">{{ __('reviews.form.submitting') }}</span>

                 </button>

             </form>

         </template>

     </div>
 </div>
@once
    @push('scripts')
        <script>
            function reviewModal() {
                return {
                    open: false,
                    success: false,

                    name: '',
                    email: '',
                    comment: '',
                    rating: 0,
                    hover: 0,
                    website: '',
                    error: '',
                    loading: false,

                    submitReview() {

                        if (this.rating === 0) {
                            this.error = "{{ __('reviews.errors.rating_required') }}"
                            return
                        }

                        this.error = ''
                        this.loading = true

                        fetch("{{ route('reviews.store') }}", {

                                method: "POST",

                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                    "Accept": "application/json"
                                },

                                body: JSON.stringify({
                                    name: this.name,
                                    email: this.email,
                                    comment: this.comment,
                                    rating: this.rating,
                                    source: "{{ $source }}",
                                    source_id: "{{ $sourceId }}",
                                    website: this.website
                                })

                            })
                            .then(res => res.json())
                            .then(data => {

                                this.loading = false

                                if (data.success) {

                                    this.success = true

                                    this.name = ''
                                    this.email = ''
                                    this.comment = ''
                                    this.rating = 0

                                    setTimeout(() => {
                                        this.open = false
                                    }, 3000)

                                } else {

                                    this.error = data.message ?? "{{ __('reviews.errors.something_wrong') }}"

                                }

                            })
                            .catch(() => {

                                this.loading = false
                                this.error = "{{ __('reviews.errors.server_error') }}"

                            })

                    }
                }
            }
        </script>
    @endpush
@endonce
