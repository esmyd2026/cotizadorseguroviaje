const REGION_LABELS = {
    'South America': 'Sudamérica',
    'North America': 'Norteamérica',
    Europe: 'Europa',
    Asia: 'Asia',
    Africa: 'África',
    Oceania: 'Oceanía',
};

export function regionLabel(region) {
    return REGION_LABELS[region] ?? region;
}
