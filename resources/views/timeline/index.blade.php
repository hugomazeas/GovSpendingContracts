@extends('layouts.app')

@section('title', 'Timeline - Government Procurement Dashboard')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-sm border border-neutral-200 p-6">
        <h2 class="text-2xl font-bold text-neutral-900 mb-6">Organization Contract Timeline</h2>

        <div id="loading" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-primary-500 mb-4"></i>
            <p class="text-neutral-600">Loading timeline data...</p>
        </div>

        <div id="timeline-container" class="hidden">
            <div id="timeline-chart"></div>
        </div>
    </div>
</div>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('/timeline/data')
        .then(response => response.json())
        .then(data => {
            document.getElementById('loading').classList.add('hidden');
            document.getElementById('timeline-container').classList.remove('hidden');
            createTimeline(data);
        })
        .catch(error => {
            console.error('Error loading timeline data:', error);
            document.getElementById('loading').innerHTML = '<p class="text-red-500">Error loading data</p>';
        });
});

function createTimeline(data) {
    console.log('Timeline data received:');
    console.log('First contract:', data.timeline[0]);
    console.log('Second contract:', data.timeline[1]);
    
    const container = d3.select('#timeline-chart');
    container.selectAll('*').remove();

    const margin = { top: 60, right: 60, bottom: 60, left: 200 };
    const width = container.node().offsetWidth - margin.left - margin.right;
    const height = Math.max(600, data.organizations.length * 60) - margin.top - margin.bottom;

    const svg = container.append('svg')
        .attr('width', width + margin.left + margin.right)
        .attr('height', height + margin.top + margin.bottom);

    const clip = svg.append('defs').append('clipPath')
        .attr('id', 'clip')
        .append('rect')
        .attr('width', width)
        .attr('height', height);

    const g = svg.append('g')
        .attr('transform', `translate(${margin.left},${margin.top})`);

    const organizations = data.organizations.map(d => d.organization);
    const timelineData = data.timeline;

    const parseDate = d3.timeParse('%Y-%m-%d');
    const formatDate = d3.timeFormat('%Y-%m-%d');

    timelineData.forEach(d => {
        d.start = parseDate(d.start);
        d.end = parseDate(d.end);
    });

    // Add position (lane) to each contract to handle overlaps
    const enrichedData = [];
    const maxLanesPerOrg = 5; // Limit sublanes to keep chart reasonable
    
    organizations.forEach(org => {
        const orgContracts = timelineData.filter(d => d.organization === org);
        const lanes = [];
        
        orgContracts.forEach(contract => {
            let laneIndex = 0;
            // Find first available lane, but limit to maxLanesPerOrg
            while (laneIndex < lanes.length && laneIndex < maxLanesPerOrg) {
                const hasOverlap = lanes[laneIndex].some(existing => {
                    return (contract.start <= existing.end && contract.end >= existing.start);
                });
                if (!hasOverlap) break;
                laneIndex++;
            }
            // Create new lane if needed and under limit
            if (laneIndex === lanes.length && laneIndex < maxLanesPerOrg) {
                lanes.push([]);
            }
            // If we're over limit, use the last lane (overlaps allowed)
            if (laneIndex >= maxLanesPerOrg) {
                laneIndex = maxLanesPerOrg - 1;
            }
            
            if (!lanes[laneIndex]) lanes[laneIndex] = [];
            lanes[laneIndex].push(contract);
            
            enrichedData.push({
                ...contract,
                pos: `${org}-${laneIndex}`,
                orgIndex: organizations.indexOf(org),
                laneIndex: laneIndex,
                id: contract.id
            });
        });
    });

    const xScale = d3.scaleTime()
        .domain(d3.extent([...timelineData.map(d => d.start), ...timelineData.map(d => d.end)]))
        .range([0, width]);

    // Create unique positions for all contract lanes
    const positions = enrichedData.map(d => d.pos);
    const uniquePositions = [...new Set(positions)];

    const yScale = d3.scaleBand()
        .domain(uniquePositions)
        .range([0, height])
        .padding(0.02);

    // Hash function to generate consistent colors for vendors
    function hashString(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        return Math.abs(hash);
    }
    
    function getVendorColor(vendor) {
        const colors = [
            '#e11d48', '#7c3aed', '#059669', '#dc2626', '#2563eb', '#ea580c',
            '#be185d', '#7c2d12', '#0369a1', '#065f46', '#7e22ce', '#991b1b',
            '#1e40af', '#b45309', '#92400e', '#6b21a8', '#166534', '#f59e0b', 
            '#3730a3', '#a16207', '#10b981', '#8b5cf6', '#ef4444', '#06b6d4',
            '#8b5cf6', '#f59e0b', '#10b981', '#ef4444', '#06b6d4', '#84cc16'
        ];
        const hash = hashString(vendor || 'unknown');
        return colors[hash % colors.length];
    }

    const xAxis = d3.axisBottom(xScale);

    // Custom Y axis showing organization names
    const yAxis = d3.axisLeft(yScale)
        .tickFormat(d => d.split('-')[0]); // Show only organization name, not lane number

    const gX = g.append('g')
        .attr('class', 'x-axis')
        .attr('transform', `translate(0,${height})`)
        .call(xAxis);

    gX.selectAll('text')
        .style('font-size', '12px')
        .style('fill', '#4b5563');

    const gY = g.append('g')
        .attr('class', 'y-axis')
        .call(yAxis);

    gY.selectAll('text')
        .style('font-size', '10px')
        .style('fill', '#4b5563');

    const zoomContainer = g.append('g')
        .attr('clip-path', 'url(#clip)');

    const zoom = d3.zoom()
        .scaleExtent([0.01, 50])
        .translateExtent([[0, 0], [width, height]])
        .extent([[0, 0], [width, height]])
        .on('zoom', handleZoom);

    // Draw lanes for each position
    const lanes = zoomContainer.selectAll('.lane')
        .data(uniquePositions)
        .enter()
        .append('line')
        .attr('class', 'lane')
        .attr('x1', 0)
        .attr('x2', width)
        .attr('y1', d => yScale(d) + yScale.bandwidth() / 2)
        .attr('y2', d => yScale(d) + yScale.bandwidth() / 2)
        .style('stroke', '#f3f4f6')
        .style('stroke-width', 1);

    const tooltip = d3.select('body').append('div')
        .attr('class', 'tooltip')
        .style('position', 'absolute')
        .style('background', 'rgba(0, 0, 0, 0.8)')
        .style('color', 'white')
        .style('padding', '10px')
        .style('border-radius', '5px')
        .style('font-size', '12px')
        .style('pointer-events', 'none')
        .style('opacity', 0);

    const items = zoomContainer.selectAll('.timeline-item')
        .data(enrichedData)
        .enter()
        .append('g')
        .attr('class', 'timeline-item');

    items.each(function(d) {
        const item = d3.select(this);
        const y = yScale(d.pos) + yScale.bandwidth() / 2;

        const contractColor = getVendorColor(d.vendor);

        if (d.type === 'period') {
            item.append('rect')
                .attr('x', xScale(d.start))
                .attr('y', y - 8)
                .attr('width', Math.max(2, xScale(d.end) - xScale(d.start)))
                .attr('height', 16)
                .attr('rx', 2)
                .style('fill', contractColor)
                .style('opacity', 0.8)
                .style('stroke', '#374151')
                .style('stroke-width', 1)
                .style('cursor', 'pointer');
        } else {
            item.append('circle')
                .attr('cx', xScale(d.start))
                .attr('cy', y)
                .attr('r', 4)
                .style('fill', contractColor)
                .style('stroke', '#374151')
                .style('stroke-width', 1.5)
                .style('cursor', 'pointer');
        }
    });

    items
        .on('mouseover', function(event, d) {
            const value = d.value ? `$${(d.value / 1000000).toFixed(2)}M` : 'N/A';
            tooltip.transition().duration(200).style('opacity', 1);
            tooltip.html(`
                <strong>${d.organization}</strong><br/>
                Vendor: ${d.vendor}<br/>
                Value: ${value}<br/>
                Date: ${formatDate(d.start)}${d.type === 'period' ? ' - ' + formatDate(d.end) : ''}<br/>
                ${d.description ? d.description.substring(0, 100) + '...' : ''}<br/>
                <em style="color: #94a3b8;">Click to view details</em>
            `)
            .style('left', (event.pageX + 10) + 'px')
            .style('top', (event.pageY - 28) + 'px');
        })
        .on('mouseout', function(event, d) {
            tooltip.transition().duration(500).style('opacity', 0);
        })
        .on('click', function(event, d) {
            console.log('Contract clicked:', d);
            if (d && d.id) {
                window.location.href = `/contract/${d.id}`;
            }
        });

    const currentYear = new Date();
    const currentYearLine = zoomContainer.append('line')
        .attr('class', 'current-year-line')
        .attr('x1', xScale(currentYear))
        .attr('x2', xScale(currentYear))
        .attr('y1', -10)
        .attr('y2', height + 10)
        .style('stroke', '#dc2626')
        .style('stroke-width', 2)
        .style('stroke-dasharray', '5,5');

    const currentYearText = g.append('text')
        .attr('x', xScale(currentYear))
        .attr('y', -15)
        .attr('text-anchor', 'middle')
        .style('font-size', '12px')
        .style('font-weight', 'bold')
        .style('fill', '#dc2626')
        .text('Current Year');

    g.append('text')
        .attr('x', width / 2)
        .attr('y', -30)
        .attr('text-anchor', 'middle')
        .style('font-size', '24px')
        .style('font-weight', 'bold')
        .style('fill', '#1f2937')
        .text('Top 20 Organizations Contract Timeline (Last 20 Years)');

    svg.call(zoom);

    function handleZoom(event) {
        const { transform } = event;

        const newXScale = transform.rescaleX(xScale);

        gX.call(xAxis.scale(newXScale));

        items.selectAll('rect')
            .attr('x', d => newXScale(d.start))
            .attr('width', d => Math.max(1, newXScale(d.end) - newXScale(d.start)));

        items.selectAll('circle')
            .attr('cx', d => newXScale(d.start));

        lanes.attr('x1', newXScale.range()[0])
            .attr('x2', newXScale.range()[1]);

        currentYearLine
            .attr('x1', newXScale(currentYear))
            .attr('x2', newXScale(currentYear));

        currentYearText
            .attr('x', newXScale(currentYear));
    }
}
</script>
@endsection
