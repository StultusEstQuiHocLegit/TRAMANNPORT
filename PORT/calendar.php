<h1>🗓️ CALENDAR</h1>

<!-- Include the CSS and JavaScript from the CDN -->
<!-- <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet" /> -->
<style>
    #CalenderEventManipulator {
        position: absolute;
        z-index: 5;
        /* height: 500px; */
        width: 500px;
        background-color: #505050;
        padding: 10px;
        display: none;
    }
</style>
<script src=" https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js "></script>

<!-- Calendar Container -->
<div id="calendar"></div>


















<br><br><br>
<!-- Modal for Event Creation / Editing -->
<div id="CalenderEventManipulator" class="steps" style="display:none;">
    <h3 id="modalTitle">ADD EVENT</h3>
    <form id="eventForm">
        <input type="hidden" id="eventId" name="idpk" value="">
            <input type="text" id="eventName" name="eventname" placeholder="general assembly of flying apes" style="width: 300px;" style="width: 300px;" required>
            <label for="eventName">name*<br><div style="opacity: 0.4;">(* means that this field is required)</label>

            <br><br>
            <textarea id="eventDescription" name="eventdescription" placeholder="remember to bring some cake" style="width: 500px; height: 100px;"></textarea>
            <label for="eventDescription">description</label>

            <br><br>
            <input type="text" id="eventLocation" name="location" placeholder="cloud cuckoo land" style="width: 300px;">
            <label for="eventLocation">location</label>

            <br><br>
            <input type="checkbox" id="eventAllDay" name="allday" value="1" checked onchange="toggleAllDayFields(this)">
            <label for="eventAllDay">all day</label>

            <div id="timeFields" style="display: none;">
                <br><br>
                <input type="datetime-local" id="eventStartTime" name="starttime" style="width: 300px;">
                <label for="eventStartTime">start*</label>

                <br><br>
                <input type="datetime-local" id="eventEndTime" name="endtime" style="width: 300px;">
                <label for="eventEndTime">end*</label>
            </div>
            <br><br><br>
        <!-- <button type="submit">↗️ SAVE</button> -->
        <a href="javascript:void(0);" class="mainbutton" onclick="submitForm()">↗️ SAVE</a>
    </form>
</div>



















<script>
function submitForm() {
    // Hide the time fields
    document.getElementById('timeFields').style.display = 'none';
    
    // Trigger form submission
    document.getElementById('eventForm').requestSubmit();
}

function toggleAllDayFields(checkbox) {
    const timeFields = document.getElementById('timeFields');
    if (checkbox.checked) {
        timeFields.style.display = 'none';
    } else {
        timeFields.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const modal = document.getElementById('CalenderEventManipulator');

    // Initialize FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: function(info, successCallback, failureCallback) {
            fetch('./SaveDataCalendarGetEvents.php')
                .then(response => response.json())
                .then(data => {
                    const events = data.map(event => ({
                        id: event.idpk,
                        title: event.EventName,
                        start: event.allDay ? event.start.split('T')[0] : event.start,
                        end: event.allDay ? null : event.end,
                        allDay: event.allDay,
                        description: event.EventDescription,
                        location: event.location,
                        backgroundColor: '#505050',
                        borderColor: '#505050'
                    }));
                    successCallback(events);
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
        },
        editable: true,
        droppable: true,
        selectable: true,

        // When a date is clicked, open the modal for creating a new event
        dateClick: function(info) {
            openEventModal(info.dateStr);  // Open modal for creating a new event
        },

        // When an event is clicked, open the modal for editing the event
        eventClick: function(info) {
            openEventModal(info.event.startStr, info.event);  // Open modal for editing an event
        },

        // When an event is dragged and dropped to a new date, update it
        eventDrop: function(info) {
            const updatedEvent = {
                idpk: info.event.id,
                eventname: info.event.title,
                eventdescription: info.event.extendedProps.description,
                starttime: info.event.start.getTime() / 1000,  // Convert to UNIX timestamp
                endtime: info.event.end ? info.event.end.getTime() / 1000 : null,
                location: info.event.extendedProps.location,
                allday: info.event.allDay ? 1 : 0,
                action: 'update'  // Indicate that this is an update action
            };
        
            // Send the updated event data to the server
            fetch('./SaveDataCalendarSaveEvents.php', {
                method: 'POST',
                body: JSON.stringify(updatedEvent),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Successfully updated event
                    console.log('Event updated successfully');
                } else {
                    // Handle any error from the server
                    alert('Error updating event: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error updating event:', error);
                alert('Error updating event');
            });
        }
    });

    calendar.render();

    function openEventModal(dateStr, eventData = null) {
        document.getElementById('eventForm').reset();
        document.getElementById('modalTitle').innerText = eventData ? 'EDIT EVENT' : 'ADD EVENT';
        
        if (eventData) {
            document.getElementById('eventId').value = eventData.id;
            document.getElementById('eventName').value = eventData.title;
            document.getElementById('eventDescription').value = eventData.extendedProps.description;
            document.getElementById('eventLocation').value = eventData.extendedProps.location;
            document.getElementById('eventAllDay').checked = eventData.extendedProps.allDay;
            toggleAllDayFields(document.getElementById('eventAllDay')); // Adjust time fields visibility

            // If it's an all-day event, set start and end to the same day, without time
            if (eventData.allDay) {
                let startDate = eventData.startStr.split('T')[0]; // Extract just the date part
                document.getElementById('eventStartTime').value = startDate;  // Only date, no time
                document.getElementById('eventEndTime').value = startDate;  // Same date for end
            } else {
                // For timed events, set start and end times
                document.getElementById('eventStartTime').value = eventData.start.toISOString().slice(0, 16);
                document.getElementById('eventEndTime').value = eventData.end.toISOString().slice(0, 16);
            }
        } else {
            // If it's a new event, set the start and end times to default
            let defaultStartTime = new Date(dateStr);
            defaultStartTime.setHours(10, 0, 0, 0);
            let defaultEndTime = new Date(defaultStartTime);
            defaultEndTime.setHours(11, 0, 0, 0);
            document.getElementById('eventStartTime').value = defaultStartTime.toISOString().slice(0, 16);
            document.getElementById('eventEndTime').value = defaultEndTime.toISOString().slice(0, 16);
        }

        // Get the position of the clicked date on the calendar
        const calendarEl = document.getElementById('calendar');
        const cell = calendarEl.querySelector(`[data-date="${new Date(dateStr).toISOString().slice(0, 10)}"]`);

        if (cell) {
            const cellRect = cell.getBoundingClientRect();
            let leftPosition = cellRect.right + window.scrollX + 10;  // Right of the clicked cell
            let topPosition = cellRect.top + window.scrollY;

            const modalWidth = modal.offsetWidth;
            const windowWidth = window.innerWidth;

            if (leftPosition + modalWidth > windowWidth) {
                leftPosition = cellRect.left - modalWidth - 10;
            }

            modal.style.left = `${leftPosition}px`;
            modal.style.top = `${topPosition}px`;
        }

        modal.style.display = 'block';
    }

    // Handle form submission
    document.getElementById('eventForm').addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(this);
        const action = formData.get('idpk') ? 'update' : 'create';
        const eventData = {
            idpk: formData.get('idpk'),
            eventname: formData.get('eventname'),
            eventdescription: formData.get('eventdescription'),
            starttime: new Date(formData.get('starttime')).getTime() / 1000,
            endtime: new Date(formData.get('endtime')).getTime() / 1000,
            location: formData.get('location'),
            allday: document.getElementById('eventAllDay').checked ? 1 : 0,
            action: action
        };

        fetch('./SaveDataCalendarSaveEvents.php', {
            method: 'POST',
            body: JSON.stringify(eventData),
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                calendar.refetchEvents();
                modal.style.display = 'none';  // Close the modal after saving
            } else {
                alert('Error saving event: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error saving event:', error);
            alert('Error saving event');
        });
    });

    // Add click event listener to document to close the modal when clicked outside
    document.addEventListener('click', function(event) {
        // Check if the click was outside the modal and calendar events
        if (!modal.contains(event.target) && event.target !== calendarEl && !calendarEl.contains(event.target)) {
            modal.style.display = 'none';
        }
    });

});
</script>