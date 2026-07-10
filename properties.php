<?php include 'includes/header.php'; ?>
<?php include 'includes/navigation.php'; ?>

<?php
// Mock Data for Properties
$properties = [
    [
        'id' => '1',
        'title' => 'Tuaran Seafront Acreage',
        'location' => 'Tuaran, Sabah',
        'price' => 'MYR 2,400,000',
        'size' => '4.5 Acres',
        'type' => 'Land',
        'title_type' => 'NT',
        'image_url' => 'https://picsum.photos/seed/land1/800/600',
        'description' => 'Prime Native Title seafront land suitable for eco-resort development.',
    ],
    [
        'id' => '2',
        'title' => 'KK Industrial Park Lot',
        'location' => 'Kota Kinabalu, Sabah',
        'price' => 'MYR 5,800,000',
        'size' => '2 Acres',
        'type' => 'Commercial',
        'title_type' => 'CL',
        'image_url' => 'https://picsum.photos/seed/factory/800/600',
        'description' => 'Heavy industrial zoning, CL title (99 years), ready for logistics hub.',
    ],
    [
        'id' => '3',
        'title' => 'Penampang Hillside View',
        'location' => 'Penampang, Sabah',
        'price' => 'MYR 850,000',
        'size' => '0.8 Acres',
        'type' => 'Residential',
        'title_type' => 'NT',
        'image_url' => 'https://picsum.photos/seed/hill/800/600',
        'description' => 'Elevated residential plot with panoramic views of the valley.',
    ],
    [
        'id' => '4',
        'title' => 'Sutera Harbour Condo',
        'location' => 'Kota Kinabalu, Sabah',
        'price' => 'MYR 3,200,000',
        'size' => '2,400 sqft',
        'type' => 'Residential',
        'title_type' => 'CL',
        'image_url' => 'https://picsum.photos/seed/condo/800/600',
        'description' => 'Luxury penthouse unit, foreigner eligible (CL), fully furnished.',
    ]
];
?>

<!-- Container for the whole page -->
<div class="pt-24 pb-20 px-6 min-h-screen bg-navy-900">



    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-end mb-12 border-b border-slate-800 pb-8">
            <div>
                <h1 class="text-4xl font-serif text-white mb-2">Sabah Land & Development</h1>
                <p class="text-slate-400">Curated inventory of Native Title and Country Lease assets.</p>
            </div>

            <div class="flex space-x-2 mt-6 md:mt-0" x-data="{ filter: 'ALL' }">
                <button @click="filter = 'ALL'; filterProperties('ALL')"
                    :class="filter === 'ALL' ? 'bg-forest-900 text-white' : 'text-slate-400 border border-slate-700'"
                    class="px-4 py-2 text-sm uppercase transition-colors">All Assets</button>
                <button @click="filter = 'NT'; filterProperties('NT')"
                    :class="filter === 'NT' ? 'bg-forest-900 text-white' : 'text-slate-400 border border-slate-700'"
                    class="px-4 py-2 text-sm uppercase transition-colors">NT (Native)</button>
                <button @click="filter = 'CL'; filterProperties('CL')"
                    :class="filter === 'CL' ? 'bg-forest-900 text-white' : 'text-slate-400 border border-slate-700'"
                    class="px-4 py-2 text-sm uppercase transition-colors">CL (Country)</button>
            </div>
        </div>



        <!-- Listings Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            <?php foreach ($properties as $property): ?>
                <div class="property-card bg-navy-800 border border-slate-700 group hover:border-forest-900 transition-all duration-300"
                    data-type="<?php echo $property['title_type']; ?>">
                    <div class="relative h-64 overflow-hidden">
                        <img src="<?php echo $property['image_url']; ?>" alt="<?php echo $property['title']; ?>"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
                        <div class="absolute top-4 right-4">
                            <?php if ($property['title_type'] === 'NT'): ?>
                                <span
                                    class="px-2 py-1 text-[0.65rem] font-bold uppercase tracking-wider bg-forest-900 text-white border border-forest-700">Native
                                    Title</span>
                            <?php else: ?>
                                <span
                                    class="px-2 py-1 text-[0.65rem] font-bold uppercase tracking-wider bg-blue-900 text-white border border-blue-700">Country
                                    Lease</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-serif text-white mb-2"><?php echo $property['title']; ?></h3>
                        <p class="text-forest-800 text-sm font-bold mb-4"><?php echo $property['price']; ?></p>
                        <div class="flex text-xs text-slate-400 mb-4 space-x-4">
                            <span><?php echo $property['location']; ?></span><span>•</span><span><?php echo $property['size']; ?></span><span>•</span><span><?php echo $property['type']; ?></span>
                        </div>
                        <p class="text-slate-400 text-sm mb-6 line-clamp-2"><?php echo $property['description']; ?></p>
                        <button
                            class="w-full bg-slate-700 text-white py-2 text-xs uppercase tracking-widest hover:bg-slate-600 transition-colors">View
                            Prospectus</button>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>

<script>
    function filterProperties(type) {
        const cards = document.querySelectorAll('.property-card');
        cards.forEach(card => {
            if (type === 'ALL' || card.dataset.type === type) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }
</script>

<?php include 'includes/footer.php'; ?>