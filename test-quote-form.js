// Test script for quote form submission
import axios from 'axios';

async function testQuoteForm() {
    const testData = {
        name: "Jane Doe",
        company: "Doe Events LLC",
        email: "jane.doe@example.com",
        phone: "(504) 555-5678",
        projectType: "Event",
        services: ["Portable Toilets", "Handwash Stations"],
        startDate: "2025-10-15",
        duration: "2 days",
        location: "Baton Rouge, LA",
        message: "Planning a large outdoor event with 500+ attendees. Need portable facilities."
    };

    try {
        console.log('Sending quote request...');
        const response = await axios.post('http://admin-service-core.test/api/quotes', testData, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Origin': 'http://raw-disposal.test'
            }
        });

        console.log('✅ Success!');
        console.log('Response:', response.data);
    } catch (error) {
        console.error('❌ Error:', error.response ? error.response.data : error.message);
    }
}

testQuoteForm();