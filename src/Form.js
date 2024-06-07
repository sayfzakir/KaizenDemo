import React, { useState, useEffect, useRef } from 'react';
import './Form.css';
import Sidebar from './Sidebar'; 

function Form() {
    const [formData, setFormData] = useState({
        name: '',
        dateOfBirth: '',
        referringphysician: ''
    });
    const [questions, setQuestions] = useState([]);
    const [responses, setResponses] = useState({});
    const canvasRef = useRef(null);
    const [isDrawing, setIsDrawing] = useState(false);

    useEffect(() => {
        fetch('http://localhost:8000/getQuestions.php')
            .then(response => response.json())
            .then(data => {
                //console.log("Fetched data:", data);
                setQuestions(data.questions);
                const initialResponses = {};
                data.questions.forEach(question => {
                    if (question.type === 'embedded') {
                        const parts = question.text.split("{{input}}").length - 1;  // Determine number of inputs
                        initialResponses[question.id] = Array(parts).fill(question.defaultValue || '1').join(" || ");
                    }
                    if (question.allowOther) {
                        initialResponses[`${question.id}-other`] = ''; // Initialize 'Other' input
                    }
                    if (question.type === 'checkbox') {
                        initialResponses[question.id] = [];
                    } else {
                        initialResponses[question.id] = question.defaultValue || '';
                    }
                });
                setResponses(initialResponses);
            })
            .catch(error => console.error('Error fetching questions:', error));
    }, []);

    const startDrawing = (e) => {
        const ctx = canvasRef.current.getContext('2d');
        ctx.beginPath();
        ctx.moveTo(e.nativeEvent.offsetX, e.nativeEvent.offsetY);
        setIsDrawing(true);
    };

    const draw = (e) => {
        if (!isDrawing) return;
        const ctx = canvasRef.current.getContext('2d');
        ctx.lineTo(e.nativeEvent.offsetX, e.nativeEvent.offsetY);
        ctx.stroke();
    };

    const stopDrawing = () => {
        const ctx = canvasRef.current.getContext('2d');
        ctx.closePath();
        setIsDrawing(false);
    };

    const clearCanvas = () => {
        const ctx = canvasRef.current.getContext('2d');
        ctx.clearRect(0, 0, canvasRef.current.width, canvasRef.current.height);
    };

    const handleInitialChange = (event) => {
        const { name, value } = event.target;
        setFormData(prev => ({
            ...prev,
            [name]: value
        }));
    };

    // const handleChange = (event, questionId, isOther) => {
    //     const { name, value, type, checked } = event.target;
    //     setResponses(prev => {
    //         if (isOther) {
    //             // Directly update the value for the "Other" input
    //             return {
    //                 ...prev,
    //                 [`${questionId}-other`]: value
    //             };
    //         }else if (type === "checkbox") {
    //             const currentValues = prev[questionId] || [];
    //             if (checked) {
    //                 return {...prev, [questionId]: [...currentValues, value]};
    //             } else {
    //                 return {...prev, [questionId]: currentValues.filter(item => item !== value)};
    //             }
    //         } else {
    //             return {
    //                 ...prev,
    //                 [questionId]: value
    //             };
    //         }
    //     });
    // };

    const handleChange = (event, questionId, isOther) => {
        const { name, value, type, checked } = event.target;
        setResponses(prev => {
            const newState = {...prev};
            if (isOther) {
                newState[`${questionId}-other`] = value;
            } else if (type === "checkbox") {
                let currentValues = newState[questionId] || [];
                newState[questionId] = checked ? [...currentValues, value] : currentValues.filter(item => item !== value);
            } else {
                newState[questionId] = value;
            }
            return newState;
        });
    };
    

    const handleEmbeddedChange = (event, questionId, idx) => {
        const { value } = event.target;
        setResponses(prev => {
            const currentValues = prev[questionId].split(" || ");
            currentValues[idx] = value;
            return { ...prev, [questionId]: currentValues.join(" || ") };
        });
    };

    const handleSubmit = (event) => {
        event.preventDefault();
        console.log('Form Data:', formData);
        console.log('Responses:', responses);
    
        // Assuming your server endpoint is set to receive the form data at this URL
        const submitUrl = 'http://localhost:8000/form.php';
    
        fetch(submitUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'  // Ensure the server knows that client expects JSON
            },
            body: JSON.stringify({responses})
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Submission Success:', data);
            clearCanvas();
            resetForm();
        })
        .catch(error => {
            console.error('Submission Error:', error);
            // Handle errors here, such as displaying a notification to the user
        });
    };
    
    // Optionally, a reset form function to clear the form after successful submission
    const resetForm = () => {
        const resetResponses = {};
        questions.forEach(question => {
            if (question.type === 'checkbox') {
                resetResponses[question.id] = [];
                if (question.allowOther) {
                    resetResponses[`${question.id}-other`] = '';
                }
            } else {
                resetResponses[question.id] = question.defaultValue || '';
            }
        });
        setResponses(resetResponses);
        setFormData({
            name: '',
            dateOfBirth: '',
            referringphysician: ''
        });
    };

    const renderQuestion = (question,index) => {
        switch (question.type) {
            case 'radio':
                return (
                    <div key={question.id} className="question-container">
                        <p>{index + 1}. {question.text}</p>
                        {question.options.map(option => (
                            <label key={option}>
                                <input
                                    type={question.type}
                                    name={question.id.toString()}
                                    value={option}
                                    checked={responses[question.id].includes(option)}
                                    onChange={e => handleChange(e, question.id)}
                                /> {option}
                            </label>
                        ))}
                        {question.allowOther && (
                        <label>
                            Other (please specify):
                            <input
                                type="text"
                                placeholder="Your answer"
                                value={responses[`${question.id}-other`]}
                                onChange={(e) => handleChange(e, question.id, true)}  // Notice the `true` indicating it's an "Other" input
                                className="other-input"
                            />
                        </label>
                    )}
                    </div>
                );
            case 'checkbox':
                return (
                    <div key={question.id} className="question-container">
                        <p>{index + 1}. {question.text}</p>
                        <div className="checkbox-options">
                            {question.options.map(option => (
                                <label key={option} className='checkbox-label'>
                                    <input
                                        type={question.type}
                                        name={question.id.toString()}
                                        value={option}
                                        checked={responses[question.id].includes(option)}
                                        onChange={e => handleChange(e, question.id)}
                                    /> {option}
                                </label>
                            ))}
                        </div>
                        
                        {question.allowOther && (
                        <label>
                            Other (please specify):
                            <input
                                type="text"
                                placeholder="Your answer"
                                value={responses[`${question.id}-other`]}
                                onChange={(e) => handleChange(e, question.id, true)}  // Notice the `true` indicating it's an "Other" input
                                className="other-input"
                            />
                        </label>
                    )}
                    </div>
                );
            case 'embedded':
                const parts = question.text.split("{{input}}");
                return(
                    <div key={question.id} className="question-container">
                        <p>{index + 1}.
                            {parts.map((part, idx) => (
                                <React.Fragment key={idx}>
                                    {part}
                            {idx < parts.length - 1 && (  // Change condition to add input after the part except for the last part
                                <input
                                    type="number"
                                    value={responses[question.id].split(" || ")[idx] || ''}  // Adjust index to be unique for each input
                                    onChange={(e) => handleEmbeddedChange(e, question.id, idx)}
                                    style={{ width: '50px', margin: '0 10px' }}
                                    min="0"
                                />
                            )}
                                </React.Fragment>
                            ))}
                        </p>
                </div>)
            case 'textbox':
                return (
                    <div key={question.id} className="question-container">
                        <p>{index + 1}. {question.text}</p>
                        <textarea
                            value={responses[question.id] || ''}
                            onChange={(e) => handleChange(e, question.id)}
                            placeholder="Type your answer here..."
                            className="text-input"
                            style={{ width: '100%', height: '100px', padding: '10px' }}
                        />
                    </div>
                );
            default:
                return null;
        }
    };

    return (
        <div className="app-container">
            <Sidebar />
            <div className="content-area">
                <div className="Title">
                    <h2 style={{ color: '#025877' }}>Headache Questionare</h2>
                </div>
                <div className="main-content">
                    <div className="static-fields">
                        <input
                            type="text"
                            name="name"
                            value={formData.name}
                            onChange={handleInitialChange}
                            placeholder="Name"
                            className="text-input"
                        />
                        <input
                            type="date"
                            name="dateOfBirth"
                            value={formData.dateOfBirth}
                            onChange={handleInitialChange}
                            className="text-input"
                        />
                        <input
                            type="text"
                            name="referringphysician"
                            value={formData.referringphysician}
                            onChange={handleInitialChange}
                            placeholder="Referring Physician"
                            className="text-input"
                        />
                    </div>
                
                    <form onSubmit={handleSubmit}>
                        {questions.map((question, index) => renderQuestion(question, index))}
                    <div className="signature-section">
                        <label>Signature:</label>
                        <canvas
                            ref={canvasRef}
                            width={400}
                            height={200}
                            onMouseDown={startDrawing}
                            onMouseUp={stopDrawing}
                            onMouseMove={draw}
                            onMouseOut={stopDrawing}
                            style={{ border: '2px solid black' }}
                        />
                        <button type="button" onClick={clearCanvas} style={{ display: 'block', margin: '10px' }}>
                            Clear Signature
                        </button>
                    </div>
                        <button type="submit">Submit</button>
                    </form>
                </div>
            </div>
            
        </div>
    );
}

export default Form;